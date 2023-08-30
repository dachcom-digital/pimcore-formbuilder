<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\Event\FormAssembleEvent;
use FormBuilderBundle\Form\RuntimeData\FormRuntimeDataAllocatorInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
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

    public function assemble(FormOptionsResolver $optionsResolver): array
    {
        return $this->assembleViewVars($optionsResolver);
    }

    public function assembleViewVars(FormOptionsResolver $optionsResolver): array
    {
        $builderError = false;
        $exceptionMessage = null;
        $formDefinition = null;

        $formId = $optionsResolver->getFormId();

        if ($formId !== null) {
            try {
                $formDefinition = $this->formDefinitionManager->getById($formId);
                if (!$formDefinition instanceof FormDefinitionInterface) {
                    $exceptionMessage = sprintf('Form with id "%s" is not valid.', $formId);
                    $builderError = true;
                }
            } catch (\Exception $e) {
                $exceptionMessage = $e->getMessage();
                $builderError = true;
            }
        } else {
            $exceptionMessage = 'No valid form selected.';
            $builderError = true;
        }

        $viewVars = [];
        $viewVars['form_layout'] = $optionsResolver->getFormLayout();

        if ($builderError === true) {
            $viewVars['message'] = $exceptionMessage;
            $viewVars['form_template'] = null;
            $viewVars['form_id'] = null;

            return $viewVars;
        }

        $formAssembleEvent = new FormAssembleEvent($optionsResolver, $formDefinition);
        $this->eventDispatcher->dispatch($formAssembleEvent, FormBuilderEvents::FORM_ASSEMBLE_PRE);

        $systemRuntimeData = [
            'form_preset'             => $optionsResolver->getFormPreset(),
            'form_output_workflow'    => $optionsResolver->getOutputWorkflow(),
            'form_template'           => $optionsResolver->getFormTemplateName(),
            'form_template_full_path' => $optionsResolver->getFormTemplate(),
            'custom_options'          => $optionsResolver->getCustomOptions()
        ];

        $viewVars['form_block_template'] = $optionsResolver->getFormBlockTemplate();
        $viewVars['form_template'] = $optionsResolver->getFormTemplate();
        $viewVars['form_id'] = $optionsResolver->getFormId();
        $viewVars['form_preset'] = $optionsResolver->getFormPreset();
        $viewVars['form_output_workflow'] = $optionsResolver->getOutputWorkflow();
        $viewVars['main_layout'] = $optionsResolver->getMainLayout();

        $formRuntimeDataCollector = $this->formRuntimeDataAllocator->allocate($formDefinition, $systemRuntimeData);
        $formRuntimeData = $formRuntimeDataCollector->getData();

        $form = $this->frontendFormBuilder->buildForm($formDefinition, $formRuntimeData, $formAssembleEvent->getFormData());

        $formAssembleEvent = new FormAssembleEvent($optionsResolver, $formDefinition, $form);
        $this->eventDispatcher->dispatch($formAssembleEvent, FormBuilderEvents::FORM_ASSEMBLE_POST);

        $viewVars['form'] = $form->createView();

        return $viewVars;
    }
}
