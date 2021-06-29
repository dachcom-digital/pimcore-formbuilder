<?php

namespace FormBuilderBundle\Assembler;

use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\Form\RuntimeData\FormRuntimeDataAllocatorInterface;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class FormAssembler
{
    protected FrontendFormBuilder $frontendFormBuilder;
    protected FormDefinitionManager $formDefinitionManager;
    protected FormRuntimeDataAllocatorInterface $formRuntimeDataAllocator;
    protected string $preset = '';

    public function __construct(
        FrontendFormBuilder $frontendFormBuilder,
        FormDefinitionManager $formDefinitionManager,
        FormRuntimeDataAllocatorInterface $formRuntimeDataAllocator
    ) {
        $this->frontendFormBuilder = $frontendFormBuilder;
        $this->formDefinitionManager = $formDefinitionManager;
        $this->formRuntimeDataAllocator = $formRuntimeDataAllocator;
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
                if (!$formDefinition instanceof FormDefinitionInterface || !$this->formDefinitionManager->configurationFileExists($formId)) {
                    $errorMessage = [];
                    if (!$formDefinition instanceof FormDefinitionInterface) {
                        $errorMessage[] = sprintf('Form with id "%s" is not valid.', $formId);
                    }

                    if (!$this->formDefinitionManager->configurationFileExists($formId)) {
                        $formConfigurationPath = $this->formDefinitionManager->getConfigurationPath($formId);
                        $errorMessage[] = sprintf('Configuration file is not available. This file needs to be generated as "%s".', $formConfigurationPath);
                    }

                    $exceptionMessage = join(' ', $errorMessage);
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

        $systemRuntimeData = [
            'form_preset'          => $optionsResolver->getFormPreset(),
            'form_output_workflow' => $optionsResolver->getOutputWorkflow(),
            'form_template'        => $optionsResolver->getFormTemplateName(),
            'custom_options'       => $optionsResolver->getCustomOptions(),
            'email'                => [
                // deprecated but needed for fallback definitions in output workflows
                '_deprecated_note'      => 'This node has been deprecated in Version 3.3. Please use the email output workflow channel.',
                'send_copy'             => $optionsResolver->getSendCopy(),
                'mail_template_id'      => $optionsResolver->getMailTemplateId(),
                'copy_mail_template_id' => $optionsResolver->getCopyMailTemplateId()
            ]
        ];

        $formRuntimeDataCollector = $this->formRuntimeDataAllocator->allocate($formDefinition, $systemRuntimeData);
        $formRuntimeData = $formRuntimeDataCollector->getData();

        /** @var FormInterface $form */
        $form = $this->frontendFormBuilder->buildForm($formDefinition, $formRuntimeData);

        $viewVars['form_block_template'] = $optionsResolver->getFormBlockTemplate();
        $viewVars['form_template'] = $optionsResolver->getFormTemplate();
        $viewVars['form_id'] = $optionsResolver->getFormId();
        $viewVars['form_preset'] = $optionsResolver->getFormPreset();
        $viewVars['form_output_workflow'] = $optionsResolver->getOutputWorkflow();
        $viewVars['main_layout'] = $optionsResolver->getMainLayout();
        $viewVars['form'] = $form->createView();

        return $viewVars;
    }
}
