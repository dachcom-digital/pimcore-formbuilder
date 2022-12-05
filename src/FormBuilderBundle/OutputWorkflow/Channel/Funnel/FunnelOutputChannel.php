<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\FunnelChannelType;
use FormBuilderBundle\Form\Type\LayerType;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerResponse;
use FormBuilderBundle\OutputWorkflow\Channel\FunnelAwareChannelInterface;
use FormBuilderBundle\OutputWorkflow\FunnelWorkerData;
use FormBuilderBundle\Registry\FunnelLayerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
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

        $funnelLayerResponse = new FunnelLayerResponse($funnelWorkerData);

        $funnelLayerResponse = $this->funnelLayerRegistry
            ->get($funnelConfiguration['type'])
            ->buildResponse($funnelLayerResponse, $formBuilder);

        $form = $formBuilder->getForm();

        $form->handleRequest($funnelWorkerData->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $target = null;
            foreach ($form->all() as $formType) {
                if ($formType instanceof SubmitButton && $formType->isClicked() === true) {
                    $target = $formType->getName();
                    break;
                }
            }

            if ($target !== null) {

                $funnelWorkerData->getFunnelFormRuntimeData()->addFunnelFormData($funnelName, $form->getData());

                $targetPath = $funnelWorkerData->getFunnelActionElementStack()->getByName($target);

                if ($targetPath instanceof FunnelActionElement) {
                    // redirect!
                    return new RedirectResponse($targetPath->getPath());
                }

                $form->addError(new FormError(sprintf('No target path for "%s" found', $target)));
            }

            $form->addError(new FormError('No funnel target found'));
        }

        $viewArguments = array_merge($funnelLayerResponse->getFunnelLayerViewArguments(), [
            'form'          => $form->createView(),
            'formTheme'  => $funnelWorkerData->getSubmissionEvent()->getFormRuntimeData()['form_template'] ?? null,
            'funnelActions' => $funnelWorkerData->getFunnelActionElementStack(),
        ]);

        $templateArguments = [
            'renderType' => $funnelLayerResponse->getRenderType(),
            'view'       => $funnelLayerResponse->getFunnelLayerView()
        ];

        if ($funnelLayerResponse->getRenderType() === FunnelLayerResponse::RENDER_TYPE_PRERENDER) {

            $template = $this->renderer->createTemplate($this->templating->render(
                $funnelLayerResponse->getFunnelLayerView(),
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
                    $funnelLayerResponse->getFunnelLayerViewArguments()
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
}
