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
use Pimcore\Model\Document\Tag\Area\Info;
use Pimcore\Model\Document\Tag\Select;
use Pimcore\Translation\Translator;

class Form extends AbstractTemplateAreabrick
{
    /**
     * @var FormDefinitionManager
     */
    protected $formDefinitionManager;

    /**
     * @var PresetManager
     */
    protected $presetManager;

    /**
     * @var FormAssembler
     */
    protected $formAssembler;

    /**
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param FormDefinitionManager $formDefinitionManager
     * @param PresetManager         $presetManager
     * @param FormAssembler         $formAssembler
     * @param TemplateManager       $templateManager
     * @param Translator            $translator
     */
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

    /**
     * @param Info $info
     *
     * @throws \Exception
     */
    public function action(Info $info)
    {
        $formId = null;
        $view = $info->getView();
        $isEditMode = $view->get('editmode');

        $info->setParams(array_merge($info->getParams(), ['forceEditInView' => true]));

        /** @var Select $formPresetSelection */
        $formPresetSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formPreset');
        /** @var Select $formTemplateSelection */
        $formTemplateSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formType');
        /** @var Select $outputWorkflowSelection */
        $outputWorkflowSelection = $this->getDocumentTag($info->getDocument(), 'select', 'outputWorkflow');
        /** @var Select $formNameElement */
        $formNameElement = $this->getDocumentTag($info->getDocument(), 'select', 'formName');

        if (!$formNameElement->isEmpty()) {
            $formId = (int) $formNameElement->getData();
        }

        // editmode variable is not available if there is an edit window
        $view->getParameters()->set('form_builder_is_admin_mode', $isEditMode === true);

        $editViewVars = [];
        if ($isEditMode === true) {
            $editViewVars = $this->prepareEditModeData($info, $formId);
        }

        $formTemplate = $formTemplateSelection->getValue();
        $sendCopy = $this->getDocumentTag($info->getDocument(), 'checkbox', 'userCopy')->getData() === true;
        $formPreset = $formPresetSelection->getData();
        $formOutputWorkflow = $outputWorkflowSelection->isEmpty() || $outputWorkflowSelection->getData() === 'none' ? null : (int) $outputWorkflowSelection->getData();

        $mailTemplate = $this->getDocumentTag($info->getDocument(), 'relation', 'sendMailTemplate')->getElement();
        $copyMailTemplate = $this->getDocumentTag($info->getDocument(), 'relation', 'sendCopyMailTemplate')->getElement();

        $optionBuilder = new FormOptionsResolver();
        $optionBuilder->setFormId($formId);
        $optionBuilder->setFormTemplate($formTemplate);
        $optionBuilder->setSendCopy($sendCopy);
        $optionBuilder->setMailTemplate($mailTemplate);
        $optionBuilder->setCopyMailTemplate($copyMailTemplate);
        $optionBuilder->setFormPreset($formPreset);
        $optionBuilder->setOutputWorkflow($formOutputWorkflow);

        $this->formAssembler->setFormOptionsResolver($optionBuilder);
        $assemblerViewVars = $this->formAssembler->assembleViewVars();

        foreach (array_merge($editViewVars, $assemblerViewVars) as $var => $varValue) {
            $view->getParameters()->set($var, $varValue);
        }
    }

    /**
     * @param Info     $info
     * @param int|null $selectedFormId
     *
     * @return array
     */
    protected function prepareEditModeData(Info $info, $selectedFormId)
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

    /**
     * @param Info  $info
     * @param array $editViewVars
     *
     * @return array
     */
    protected function prepareFormTemplateEditModeStore(Info $info, $editViewVars)
    {
        /** @var Select $formTemplateSelection */
        $formTemplateSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formType');

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

    /**
     * @param Info  $info
     * @param array $editViewVars
     *
     * @return array
     */
    protected function prepareFormPresetsEditModeStore(Info $info, $editViewVars)
    {
        $formPresetsStore = [];
        $formPresetsInfo = [];

        /** @var Select $formPresetSelection */
        $formPresetSelection = $this->getDocumentTag($info->getDocument(), 'select', 'formPreset');

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

    /**
     * @param Info     $info
     * @param array    $editViewVars
     * @param array    $formOutputWorkflows
     * @param int|null $selectedFormId
     *
     * @return array
     */
    protected function prepareOutputWorkflowEditModeStore(Info $info, $editViewVars, $formOutputWorkflows, $selectedFormId)
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

    /**
     * @return bool
     */
    public function hasEditTemplate()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getViewTemplate()
    {
        return 'FormBuilderBundle:Form:form.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getEditTemplate()
    {
        return 'FormBuilderBundle:Areas/form:edit.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Form';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagOpen(Info $info)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagClose(Info $info)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return '/bundles/formbuilder/img/application_form.svg';
    }
}
