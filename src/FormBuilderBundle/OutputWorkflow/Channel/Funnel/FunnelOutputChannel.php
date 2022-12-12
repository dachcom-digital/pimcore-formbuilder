<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\FunnelChannelType;
use FormBuilderBundle\Form\Type\LayerType;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerData;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerInterface;
use FormBuilderBundle\OutputWorkflow\Channel\FunnelAwareChannelInterface;
use FormBuilderBundle\OutputWorkflow\FunnelWorkerData;
use FormBuilderBundle\Registry\FunnelLayerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

class FunnelOutputChannel implements ChannelInterface, FunnelAwareChannelInterface
{
    protected EngineInterface $templating;
    protected SerializerInterface $serializer;
    protected FunnelLayerRegistry $funnelLayerRegistry;
    protected FormFactoryInterface $formFactory;
    protected Environment $renderer;

    public function __construct(
        EngineInterface $templating,
        SerializerInterface $serializer,
        FunnelLayerRegistry $funnelLayerRegistry,
        FormFactoryInterface $formFactory,
        Environment $renderer
    ) {
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->funnelLayerRegistry = $funnelLayerRegistry;
        $this->formFactory = $formFactory;
        $this->renderer = $renderer;
    }

    public function getFormType(): string
    {
        return FunnelChannelType::class;
    }

    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    public function getUsedFormFieldNames(array $channelConfiguration): array
    {
        return [];
    }

    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        throw new GuardException('Not supported');
    }

    public function dispatchFunnelProcessing(FunnelWorkerData $funnelWorkerData): Response
    {
        $funnelConfiguration = $funnelWorkerData->getChannel()->getConfiguration();
        $funnelName = $funnelWorkerData->getChannel()->getName();

        $formBuilder = $this->formFactory->createNamedBuilder(
            $funnelName,
            LayerType::class,
            null,
            [
                'funnel_action_element_stack' => $funnelWorkerData->getFunnelActionElementStack(),
                'workflow_name'               => $funnelWorkerData->getOutputWorkflow()->getName(),
                'funnel_name'                 => $funnelName,
            ]
        );

        $funnelLayerData = new FunnelLayerData(
            $funnelWorkerData->getRequest(),
            $funnelWorkerData->getSubmissionEvent(),
            $funnelConfiguration['configuration'] ?? [],
        );

        $funnelLayer = $this->funnelLayerRegistry->get($funnelConfiguration['type']);
        $funnelLayer->buildForm($funnelLayerData, $formBuilder);

        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($funnelWorkerData) {

            $form = $event->getForm();

            try {
                $this->findFunnelTargetAction($funnelWorkerData, $form);
            } catch (\Throwable $e) {
                $form->addError(new FormError($e->getMessage()));
            }

        }, 250);

        $form = $formBuilder->getForm();
        $form->handleRequest($funnelWorkerData->getRequest());

        if ($form->isSubmitted()) {

            // exception already handled in post submit event!
            $funnelTargetAction = $this->findFunnelTargetAction($funnelWorkerData, $form);

            if ($funnelTargetAction->ignoreInvalidSubmission() === true) {
                return new RedirectResponse($funnelTargetAction->getPath());
            }

            if ($form->isValid()) {

                if (!empty($form->getData())) {
                    $handledFormData = $funnelLayer->handleFormData($funnelLayerData, $form->getData());
                    $funnelWorkerData->getFormStorageData()->addFunnelRuntimeData($funnelName, $handledFormData);
                }

                return new RedirectResponse($funnelTargetAction->getPath());
            }
        }

        $funnelLayer->buildView($funnelLayerData);

        $viewArguments = array_merge($funnelLayerData->getFunnelLayerViewArguments(), [
            'form'          => $form->createView(),
            'formTheme'     => $funnelWorkerData->getSubmissionEvent()->getFormRuntimeData()['form_template'] ?? null,
            'funnelActions' => $funnelWorkerData->getFunnelActionElementStack(),
        ]);

        $templateArguments = [
            'renderType' => $funnelLayerData->getRenderType(),
            'view'       => $funnelLayerData->getFunnelLayerView()
        ];

        if ($funnelLayerData->getRenderType() === FunnelLayerData::RENDER_TYPE_PRERENDER) {

            $template = $this->renderer->createTemplate($this->templating->render(
                $funnelLayerData->getFunnelLayerView(),
                $viewArguments
            ));

            $templateArguments['view'] = $template->render($viewArguments);
        }

        $template = $this->templating->render(
            '@FormBuilder/funnel/base.html.twig',
            array_merge($viewArguments, $templateArguments)
        );

        if ($funnelWorkerData->getRequest()->isXmlHttpRequest()) {

            $jsonArguments = $this->serializer instanceof NormalizerInterface ? $this->serializer->normalize(
                array_merge(
                    [
                        'funnelActions' => $funnelWorkerData->getFunnelActionElementStack()->getAll(),
                    ],
                    $funnelLayerData->getFunnelLayerViewArguments()
                ), null, ['groups' => ['FunnelOutput']]
            ) : [];

            return new JsonResponse(
                array_merge(
                    [
                        'success'  => true,
                        'template' => $template,
                    ],
                    $jsonArguments
                )
            );
        }

        return new Response($template);
    }

    public function getFunnelLayer(array $funnelConfiguration): FunnelLayerInterface
    {
        return $this->funnelLayerRegistry->get($funnelConfiguration['type']);
    }

    /**
     * @throws \RuntimeException
     */
    private function findFunnelTargetAction(FunnelWorkerData $funnelWorkerData, FormInterface $form): FunnelActionElement
    {
        $target = null;

        foreach ($form->all() as $formType) {

            if ($formType instanceof SubmitButton && $formType->isClicked()) {
                $target = $formType->getName();
                break;
            }
        }

        if ($target === null) {
            throw new \RuntimeException('No funnel target found');
        }

        $funnelTargetAction = $funnelWorkerData->getFunnelActionElementStack()->getByName($target);
        if (!$funnelTargetAction instanceof FunnelActionElement) {
            throw new \RuntimeException(sprintf('No target path for "%s" found', $target));
        }

        return $funnelTargetAction;
    }
}
