<?php

namespace FormBuilderBundle\Document\Areabrick\Form;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Manager\PresetManager;
use FormBuilderBundle\Assembler\FormAssembler;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Editable\Area\Info;
use Pimcore\Model\Document\Editable\Relation;
use Pimcore\Model\Document\Editable\Select;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

class Form extends AbstractTemplateAreabrick
{
    protected FormDefinitionManager $formDefinitionManager;
    protected PresetManager $presetManager;
    protected FormAssembler $formAssembler;
    protected TemplateManager $templateManager;
    protected Translator $translator;

    public function __construct(
        FormDefinitionManager $formDefinitionManager,
        PresetManager $presetManager,
        FormAssembler $formAssembler,
        TemplateManager $templateManager,
        Translator $translator
    ) {
        $this->formDefinitionManager = $formDefinitionManager;
        $this->presetManager = $presetManager;
        $this->formAssembler = $formAssembler;
        $this->templateManager = $templateManager;
        $this->translator = $translator;
    }

    public function action(Info $info): ?Response
    {
        $formId = null;
        $isEditMode = $info->getParam('editmode');

        $info->setParams(array_merge($info->getParams(), ['forceEditInView' => true]));

        /** @var Select $formPresetSelection */
        $formPresetSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formPreset');
        /** @var Select $formTemplateSelection */
        $formTemplateSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formType');
        /** @var Select $outputWorkflowSelection */
        $outputWorkflowSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'outputWorkflow');
        /** @var Select $formNameElement */
        $formNameElement = $this->getDocumentEditable($info->getDocument(), 'select', 'formName');

        if (!$formNameElement->isEmpty()) {
            $formId = (int) $formNameElement->getData();
        }

        // editmode variable is not available if there is an edit window
        $info->setParam('form_builder_is_admin_mode', $isEditMode === true);

        $editViewVars = [];
        if ($isEditMode === true) {
            $editViewVars = $this->prepareEditModeData($info, $formId);
        }

        $formTemplate = $formTemplateSelection->getValue();
        $sendCopy = $this->getDocumentEditable($info->getDocument(), 'checkbox', 'userCopy')->getData() === true;
        $formPreset = $formPresetSelection->getData();
        $formOutputWorkflow = $outputWorkflowSelection->isEmpty() || $outputWorkflowSelection->getData() === 'none' ? null : (int) $outputWorkflowSelection->getData();

        /** @var Relation $mailTemplateElement */
        $mailTemplateElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'sendMailTemplate');
        /** @var Relation $copyMailTemplateElement */
        $copyMailTemplateElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'sendCopyMailTemplate');

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($formId);
        $optionBuilder->setFormTemplate($formTemplate);
        $optionBuilder->setSendCopy($sendCopy);
        $optionBuilder->setMailTemplate($mailTemplateElement->getElement());
        $optionBuilder->setCopyMailTemplate($copyMailTemplateElement->getElement());
        $optionBuilder->setFormPreset($formPreset);
        $optionBuilder->setOutputWorkflow($formOutputWorkflow);

        $assemblerViewVars = $this->formAssembler->assembleViewVars($optionBuilder);

        foreach (array_merge($editViewVars, $assemblerViewVars) as $var => $varValue) {
            $info->setParam($var, $varValue);
        }

        return null;
    }

    protected function prepareEditModeData(Info $info, ?int $selectedFormId): array
    {
        $editViewVars = [];
        $availableForms = [];
        $formOutputWorkflows = [];

        $allFormDefinitions = $this->formDefinitionManager->getAll();

        if (!empty($allFormDefinitions)) {
            /** @var FormDefinitionInterface $form */
            foreach ($allFormDefinitions as $form) {
                $availableForms[] = [$form->getId(), $form->getName()];
                $formOutputWorkflows[$form->getId()] = array_map(function (OutputWorkflowInterface $outputWorkflow) {
                    return [$outputWorkflow->getId(), $outputWorkflow->getName()];
                }, $form->getOutputWorkflows()->toArray());
            }
        }

        $editViewVars['formStore'] = $availableForms;

        $editViewVars = $this->prepareFormTemplateEditModeStore($info, $editViewVars);
        $editViewVars = $this->prepareFormPresetsEditModeStore($info, $editViewVars);
        $editViewVars = $this->prepareOutputWorkflowEditModeStore($info, $editViewVars, $formOutputWorkflows, $selectedFormId);

        return $editViewVars;
    }

    protected function prepareFormTemplateEditModeStore(Info $info, array $editViewVars): array
    {
        /** @var Select $formTemplateSelection */
        $formTemplateSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formType');

        $formTemplateStore = [];
        foreach ($this->templateManager->getFormTemplates(true) as $template) {
            $template[1] = $this->translator->trans($template[1], [], 'admin');
            $formTemplateStore[] = $template;
        }

        $editViewVars['formTemplateStore'] = $formTemplateStore;

        if ($formTemplateSelection->isEmpty()) {
            $formTemplateSelection->setDataFromResource($this->templateManager->getDefaultFormTemplate());
        }

        return $editViewVars;
    }

    protected function prepareFormPresetsEditModeStore(Info $info, array $editViewVars): array
    {
        $formPresetsStore = [];
        $formPresetsInfo = [];

        /** @var Select $formPresetSelection */
        $formPresetSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formPreset');

        $formPresets = $this->presetManager->getAll($info->getDocument());

        if (!empty($formPresets)) {
            $formPresetsStore[] = ['custom', $this->translator->trans('form_builder.area.no_form_preset', [], 'admin')];

            foreach ($formPresets as $presetName => $preset) {
                $formPresetsStore[] = [$presetName, $preset['nice_name']];
                $formPresetsInfo[] = $this->presetManager->getDataForPreview($presetName, $preset);
            }

            if ($formPresetSelection->isEmpty()) {
                $formPresetSelection->setDataFromResource('custom');
            }

            $editViewVars['formPresetStore'] = $formPresetsStore;
            $editViewVars['formPresetsInfo'] = $formPresetsInfo;
        }

        return $editViewVars;
    }

    protected function prepareOutputWorkflowEditModeStore(Info $info, array $editViewVars, array $formOutputWorkflows, ?int $selectedFormId): array
    {
        /** @var Select $outputWorkflowSelection */
        $outputWorkflowSelection = $this->getDocumentTag($info->getDocument(), 'select', 'outputWorkflow');

        $validWorkflowIdsForCurrentSelection = [];
        $formOutputWorkflowStore = [['none', $this->translator->trans('form_builder.area.no_output_workflow', [], 'admin')]];
        $preSelectedOutputWorkflow = 'none';
        $hasValidOutputWorkflows = false;

        if ($selectedFormId !== null && isset($formOutputWorkflows[$selectedFormId]) && count($formOutputWorkflows[$selectedFormId]) > 0) {
            $hasValidOutputWorkflows = true;
            $formOutputWorkflowStore = [];
            foreach ($formOutputWorkflows[$selectedFormId] as $index => $outputWorkflow) {
                if ($index === 0) {
                    $preSelectedOutputWorkflow = $outputWorkflow[0];
                }

                $validWorkflowIdsForCurrentSelection[] = $outputWorkflow[0];
                $formOutputWorkflowStore[] = [$outputWorkflow[0], $outputWorkflow[1]];
            }
        }

        $currentSelectionIsInvalid = false;
        if ($outputWorkflowSelection->isEmpty() === false) {
            $currentSelection = $outputWorkflowSelection->getData();
            if (is_numeric($currentSelection) && !in_array((int) $currentSelection, $validWorkflowIdsForCurrentSelection)) {
                $currentSelectionIsInvalid = true;
            }
        }

        if ($outputWorkflowSelection->isEmpty() || $currentSelectionIsInvalid === true) {
            $outputWorkflowSelection->setDataFromResource($preSelectedOutputWorkflow);
        }

        $editViewVars['hasValidOutputWorkflows'] = $hasValidOutputWorkflows;
        $editViewVars['outputWorkflowStore'] = $formOutputWorkflowStore;
        $editViewVars['allOutputWorkflowStore'] = $formOutputWorkflows;

        return $editViewVars;
    }

    public function hasEditTemplate(): bool
    {
        return true;
    }

    public function getViewTemplate(): string
    {
        return 'FormBuilderBundle:Form:form.' . $this->getTemplateSuffix();
    }

    public function getEditTemplate(): string
    {
        return 'FormBuilderBundle:Areas/form:edit.' . $this->getTemplateSuffix();
    }

    public function getTemplateSuffix(): string
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    public function getName(): string
    {
        return 'Form';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getHtmlTagOpen(Info $info): string
    {
        return '';
    }

    public function getHtmlTagClose(Info $info): string
    {
        return '';
    }

    public function getIcon(): string
    {
        return '/bundles/formbuilder/img/application_form.svg';
    }
}
