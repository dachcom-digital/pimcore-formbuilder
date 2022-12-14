<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Assembler\FunnelActionElementAssembler;
use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\FormValuesInputApplierInterface;
use FormBuilderBundle\Model\FormStorageData;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelResponseAwareInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionElementStack;
use FormBuilderBundle\OutputWorkflow\Channel\FunnelAwareChannelInterface;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use FormBuilderBundle\Registry\StorageProviderRegistry;
use FormBuilderBundle\Resolver\FunnelDataResolver;
use FormBuilderBundle\Storage\StorageProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FunnelWorker implements FunnelWorkerInterface
{
    protected Configuration $configuration;
    protected FormValuesInputApplierInterface $formValuesInputApplier;
    protected StorageProviderRegistry $storageProviderRegistry;
    protected OutputWorkflowChannelRegistry $channelRegistry;
    protected FrontendFormBuilder $frontendFormBuilder;
    protected FunnelActionElementAssembler $funnelActionElementAssembler;
    protected FunnelDataResolver $funnelDataResolver;

    public function __construct(
        Configuration $configuration,
        FormValuesInputApplierInterface $formValuesInputApplier,
        StorageProviderRegistry $storageProviderRegistry,
        OutputWorkflowChannelRegistry $channelRegistry,
        FrontendFormBuilder $frontendFormBuilder,
        FunnelActionElementAssembler $funnelActionElementAssembler,
        FunnelDataResolver $funnelDataResolver,
    ) {
        $this->configuration = $configuration;
        $this->formValuesInputApplier = $formValuesInputApplier;
        $this->storageProviderRegistry = $storageProviderRegistry;
        $this->channelRegistry = $channelRegistry;
        $this->frontendFormBuilder = $frontendFormBuilder;
        $this->funnelActionElementAssembler = $funnelActionElementAssembler;
        $this->funnelDataResolver = $funnelDataResolver;
    }

    /**
     * @throws \Exception
     */
    public function initiateFunnel(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void
    {
        if (!$outputWorkflow->isFunnelWorkflow()) {
            throw new \Exception(sprintf('Funnel with id %d is not a valid funnel', $outputWorkflow->getId()));
        }

        // get first channel
        $channels = $outputWorkflow->getChannels();

        if (count($channels) === 0) {
            throw new \Exception(sprintf('Funnel with id %d does not provide any channels', $outputWorkflow->getId()));
        }

        /** @var FormDataInterface $formData */
        $formData = $submissionEvent->getForm()->getData();

        $initiationPath = $submissionEvent->getRequest()->getPathInfo();

        if ($submissionEvent->getRequest()->isXmlHttpRequest()) {
            $startOffUrlInfo = parse_url($submissionEvent->getRequest()->headers->get('referer', ''));
            $initiationPath = $startOffUrlInfo['path'] ?? null;
        }

        $formStorageData = new FormStorageData(
            $formData->getFormDefinition()->getId(),
            $formData->getData(),
            $submissionEvent->getFormRuntimeData(),
            $initiationPath
        );

        $storageProvider = $this->getStorageProvider();
        $storageToken = $storageProvider->store($submissionEvent->getRequest(), $formStorageData);

        /** @var OutputWorkflowChannelInterface $firstChannel */
        $firstChannel = $channels[0];

        $channelProcessor = $this->channelRegistry->get($firstChannel->getType());

        $funnelActionElement = $this->funnelActionElementAssembler->assembleItem(
            $firstChannel,
            $channelProcessor,
            [
                'type'          => 'channelAction',
                'triggerName'   => '__INITIATE_FUNNEL',
                'configuration' => [
                    'channelName' => $firstChannel->getName()
                ]
            ],
            [
                'storageToken'   => $storageToken,
                'initiationPath' => $formStorageData->getInitiationPath()
            ]
        );

        $submissionEvent->setRedirectUri($funnelActionElement->getPath());
    }

    /**
     * @throws \Exception
     */
    public function processFunnel(OutputWorkflowInterface $outputWorkflow, Request $request): Response
    {
        $funnelData = $this->funnelDataResolver->getFunnelData($request);

        if (!$funnelData instanceof FunnelData) {
            throw new \Exception($request, 'Invalid Funnel Data');
        }

        if (!$outputWorkflow->isFunnelWorkflow()) {
            throw new \Exception(sprintf('Funnel with id %d is not a valid funnel', $outputWorkflow->getId()));
        }

        $storageToken = $funnelData->getStorageToken();

        $channel = $outputWorkflow->getChannelByName($funnelData->getChannelId());

        if (!$channel instanceof OutputWorkflowChannelInterface) {
            throw new \Exception(sprintf('Channel with id %d not found', $funnelData->getChannelId()));
        }

        $formStorageData = $funnelData->getFormStorageData();

        if (!$formStorageData instanceof FormStorageData) {
            throw new \Exception(sprintf('No storage data for token "%s" found', $storageToken));
        }

        // restore submission event to allow seamless output workflow channel processing
        $submissionEvent = $this->buildSubmissionEvent($request, $outputWorkflow, $formStorageData);

        $channelProcessor = $this->channelRegistry->get($channel->getType());

        $funnelWorkerData = new FunnelWorkerData(
            $funnelData,
            $submissionEvent,
            $outputWorkflow,
            $channel,
            $channelProcessor
        );

        // it's a layout funnel
        if ($channelProcessor instanceof FunnelAwareChannelInterface) {

            $funnelWorkerData->setFunnelActionElementStack($this->buildFunnelActionElementStack($funnelWorkerData));

            $funnelResponse = $channelProcessor->dispatchFunnelProcessing($funnelWorkerData);

            if ($formStorageData->funnelRuntimeDataDirty()) {
                $this->getStorageProvider()->update($request, $storageToken, $formStorageData);
                $formStorageData->resetRuntimeDataDirty();
            }

            return $funnelResponse;
        }

        // it's a virtual funnel, process channel
        return $this->processDataFunnel($funnelWorkerData);
    }

    protected function processDataFunnel(FunnelWorkerData $funnelWorkerData): Response
    {
        $dataChannelResponse = null;
        $channelProcessor = $funnelWorkerData->getChannelProcessor();

        try {

            $arguments = [
                $funnelWorkerData->getSubmissionEvent(),
                $funnelWorkerData->getOutputWorkflow()->getName(),
                $funnelWorkerData->getChannel()->getConfiguration()
            ];

            if ($channelProcessor instanceof ChannelResponseAwareInterface) {
                $dataChannelResponse = $channelProcessor->dispatchResponseOutputProcessing(...$arguments);
            } else {
                $channelProcessor->dispatchOutputProcessing(...$arguments);
            }

        } catch (\Throwable $e) {

            $funnelErrorToken = $funnelWorkerData->getFormStorageData()->addFunnelError($e->getMessage());

            $this->getStorageProvider()->update(
                $funnelWorkerData->getRequest(),
                $funnelWorkerData->getStorageToken(),
                $funnelWorkerData->getFormStorageData()
            );

            $funnelWorkerData->setFunnelActionElementStack($this->buildFunnelActionElementStack($funnelWorkerData, $funnelErrorToken));

            $virtualFunnelError = $funnelWorkerData->getFunnelActionElementStack()->getByName('virtualFunnelError');
            if (!$virtualFunnelError instanceof FunnelActionElement) {
                throw new \Exception('Could not resolve virtual error funnel');
            }

            return new RedirectResponse($virtualFunnelError->getPath());
        }

        if ($dataChannelResponse instanceof Response) {
            return $dataChannelResponse;
        }

        $funnelWorkerData->setFunnelActionElementStack($this->buildFunnelActionElementStack($funnelWorkerData));

        $virtualFunnelSuccess = $funnelWorkerData->getFunnelActionElementStack()->getByName('virtualFunnelSuccess');
        if (!$virtualFunnelSuccess instanceof FunnelActionElement) {
            throw new \Exception('Could not resolve virtual success funnel');
        }

        if ($virtualFunnelSuccess->isChannelAware()) {
            /** @var OutputWorkflowChannelInterface $nextVirtualChannel */
            $nextVirtualChannel = $virtualFunnelSuccess->getSubject();
            $nextVirtualChannelProcessor = $this->channelRegistry->get($nextVirtualChannel->getType());

            // instance dispatch is allowed for virtual funnels only!
            if ($nextVirtualChannelProcessor instanceof FunnelAwareChannelInterface) {
                return new RedirectResponse($virtualFunnelSuccess->getPath());
            }

            $nextVirtualFunnelWorkerData = new FunnelWorkerData(
                $funnelWorkerData->getFunnelData(),
                $funnelWorkerData->getSubmissionEvent(),
                $funnelWorkerData->getOutputWorkflow(),
                $nextVirtualChannel,
                $nextVirtualChannelProcessor
            );

            return $this->processDataFunnel($nextVirtualFunnelWorkerData);
        }

        return new RedirectResponse($virtualFunnelSuccess->getPath());
    }

    protected function buildFunnelActionElementStack(FunnelWorkerData $funnelWorkerData, ?string $funnelErrorToken = null): FunnelActionElementStack
    {
        $context = [
            'storageToken'   => $funnelWorkerData->getStorageToken(),
            'initiationPath' => $funnelWorkerData->getFormStorageData()->getInitiationPath()
        ];

        if ($funnelErrorToken !== null) {
            $context['errorToken'] = $funnelErrorToken;
        }

        return $this->funnelActionElementAssembler->assembleStack(
            $funnelWorkerData->getChannel(),
            $funnelWorkerData->getChannelProcessor(),
            $context
        );
    }

    protected function buildSubmissionEvent(
        Request $request,
        OutputWorkflowInterface $outputWorkflow,
        FormStorageData $formStorageData
    ): SubmissionEvent {

        $formRuntimeData = $formStorageData->getFormRuntimeData() ?? [];
        $formData = $this->formValuesInputApplier->apply($formStorageData->getFormData(), $outputWorkflow->getFormDefinition());

        $form = $this->frontendFormBuilder->buildForm(
            $outputWorkflow->getFormDefinition(),
            $formRuntimeData,
            $formData
        );

        return new SubmissionEvent($request, $formRuntimeData, $form, $formStorageData->getFunnelRuntimeData());
    }

    protected function getStorageProvider(): StorageProviderInterface
    {
        $funnelConfiguration = $this->configuration->getConfig('funnel');

        return $this->storageProviderRegistry->get($funnelConfiguration['storage_provider']);
    }
}
