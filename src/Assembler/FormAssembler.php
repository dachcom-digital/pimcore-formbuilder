<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\Event\FormAssembleEvent;
use FormBuilderBundle\Form\RuntimeData\FormRuntimeDataAllocatorInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FormAssembler
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected FrontendFormBuilder $frontendFormBuilder,
        protected FormDefinitionManager $formDefinitionManager,
        protected FormRuntimeDataAllocatorInterface $formRuntimeDataAllocator
    ) {
    }

    /**
     * @throws \Exception
     */
    public function assemble(FormOptionsResolver $optionsResolver): array
    {
        try {
            $formDefinition = $this->getFormDefinition($optionsResolver);
        } catch (\Throwable $e) {
            return [
                'message'       => $e->getMessage(),
                'form_layout'   => $optionsResolver->getFormLayout(),
                'form_template' => null,
                'form_id'       => null
            ];
        }

        $formAssembleEvent = $this->dispatchAssembleEvent(
            FormBuilderEvents::FORM_ASSEMBLE_PRE,
            [$optionsResolver, $formDefinition]
        );

        $viewVars = $this->getViewVars($optionsResolver);

        $form = $this->buildForm(
            $formDefinition,
            $optionsResolver,
            $formAssembleEvent->getFormData(),
        );

        $this->dispatchAssembleEvent(
            FormBuilderEvents::FORM_ASSEMBLE_POST,
            [$optionsResolver, $formDefinition, $form]
        );

        $viewVars['form'] = $form->createView();

        return $viewVars;
    }

    /**
     * @throws \Exception
     */
    public function assembleHeadlessForm(FormOptionsResolver $optionsResolver): FormInterface
    {
        $formDefinition = $this->getFormDefinition($optionsResolver);

        $formAssembleEvent = $this->dispatchAssembleEvent(
            FormBuilderEvents::FORM_ASSEMBLE_PRE,
            [$optionsResolver, $formDefinition]
        );

        $form = $this->buildForm(
            $formDefinition,
            $optionsResolver,
            $formAssembleEvent->getFormData(),
            true
        );

        $this->dispatchAssembleEvent(
            FormBuilderEvents::FORM_ASSEMBLE_POST,
            [$optionsResolver, $formDefinition, $form]
        );

        return $form;
    }

    /**
     * @throws \Exception
     */
    public function getFormDefinition(FormOptionsResolver $optionsResolver): FormDefinitionInterface
    {
        $formId = $optionsResolver->getFormId();

        if ($formId === null) {
            throw new \Exception('No valid form selected.', 404);
        }

        $formDefinition = $this->formDefinitionManager->getById($formId);
        if (!$formDefinition instanceof FormDefinitionInterface) {
            throw new \Exception(sprintf('Form with id "%s" is not valid.', $formId), 404);
        }

        return $formDefinition;
    }

    /**
     * @throws \Exception
     */
    public function buildForm(
        FormDefinitionInterface $formDefinition,
        FormOptionsResolver $optionsResolver,
        array $formData = [],
        bool $headless = false
    ): FormInterface {

        $systemRuntimeData = $this->getSystemRuntimeData($optionsResolver, $headless);
        $formAttributes = $optionsResolver->getFormAttributes();
        $useCsrfProtection = $optionsResolver->useCsrfProtection();

        $formRuntimeDataCollector = $this->formRuntimeDataAllocator->allocate($formDefinition, $systemRuntimeData);
        $formRuntimeData = $formRuntimeDataCollector->getData();

        if ($headless === true) {
            return $this->frontendFormBuilder->buildHeadlessForm($formDefinition, $formRuntimeData, $formAttributes, $formData, $useCsrfProtection);
        }

        return $this->frontendFormBuilder->buildForm($formDefinition, $formRuntimeData, $formAttributes, $formData, $useCsrfProtection);
    }

    public function getSystemRuntimeData(FormOptionsResolver $optionsResolver, bool $headless = false): array
    {
        $data = [
            'form_preset'          => $optionsResolver->getFormPreset(),
            'form_output_workflow' => $optionsResolver->getOutputWorkflow(),
            'custom_options'       => $optionsResolver->getCustomOptions()
        ];

        return $headless ? $data : array_merge($data, [
            'form_template'           => $optionsResolver->getFormTemplateName(),
            'form_template_full_path' => $optionsResolver->getFormTemplate(),
        ]);
    }

    public function getViewVars(FormOptionsResolver $optionsResolver): array
    {
        $viewVars = [];

        $viewVars['form_layout'] = $optionsResolver->getFormLayout();
        $viewVars['form_block_template'] = $optionsResolver->getFormBlockTemplate();
        $viewVars['form_template'] = $optionsResolver->getFormTemplate();
        $viewVars['form_id'] = $optionsResolver->getFormId();
        $viewVars['form_preset'] = $optionsResolver->getFormPreset();
        $viewVars['form_output_workflow'] = $optionsResolver->getOutputWorkflow();
        $viewVars['main_layout'] = $optionsResolver->getMainLayout();

        return $viewVars;
    }

    public function dispatchAssembleEvent(string $eventName, array $arguments): FormAssembleEvent
    {
        $formAssembleEvent = new FormAssembleEvent(...$arguments);
        $this->eventDispatcher->dispatch($formAssembleEvent, $eventName);

        return $formAssembleEvent;
    }
}
