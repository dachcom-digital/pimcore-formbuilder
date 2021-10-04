<?php

namespace FormBuilderBundle\Document\Areabrick\Form;

use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Manager\PresetManager;
use FormBuilderBundle\Assembler\FormAssembler;
use Pimcore\Extension\Document\Areabrick\AbstractAreabrick;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

class Form extends AbstractAreabrick implements EditableDialogBoxInterface
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

    public function action(Document\Editable\Area\Info $info): ?Response
    {
        $formId = null;
        $isEditMode = $info->getParam('editmode');

        $info->setParams(array_merge($info->getParams(), ['forceEditInView' => true]));

        /** @var Document\Editable\Select $formPresetSelection */
        $formPresetSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formPreset');
        /** @var Document\Editable\Select $formTemplateSelection */
        $formTemplateSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'formType');
        /** @var Document\Editable\Select $outputWorkflowSelection */
        $outputWorkflowSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'outputWorkflow');
        /** @var Document\Editable\Select $formNameElement */
        $formNameElement = $this->getDocumentEditable($info->getDocument(), 'select', 'formName');

        if (!$formNameElement->isEmpty()) {
            $formId = (int) $formNameElement->getData();
        }

        // editmode variable is not available if there is an edit window
        $info->setParam('form_builder_is_admin_mode', $isEditMode === true);

        $formTemplate = $formTemplateSelection->getValue();
        $sendCopy = $this->getDocumentEditable($info->getDocument(), 'checkbox', 'userCopy')->getData() === true;
        $formPreset = $formPresetSelection->getData();
        $formOutputWorkflow = $outputWorkflowSelection->isEmpty() || $outputWorkflowSelection->getData() === 'none' ? null : (int) $outputWorkflowSelection->getData();

        /** @var Document\Editable\Relation $mailTemplateElement */
        $mailTemplateElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'sendMailTemplate');
        /** @var Document\Editable\Relation $copyMailTemplateElement */
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

        foreach ($assemblerViewVars as $var => $varValue) {
            $info->setParam($var, $varValue);
        }

        return null;
    }

    public function getEditableDialogBoxConfiguration(Document\Editable $area, ?Document\Editable\Area\Info $info): EditableDialogBoxConfiguration
    {
        $baseDocument = $area->getDocument();

        $editableDialog = new EditableDialogBoxConfiguration();

        $formId = null;
        $outputWorkflowSelection = null;

        $availableForms = [];
        $formOutputWorkflows = [];
        $validWorkflowIdsForCurrentSelection = [];

        $allFormDefinitions = $this->formDefinitionManager->getAll();

        if (!empty($allFormDefinitions)) {
            foreach ($allFormDefinitions as $form) {
                $availableForms[] = [$form->getId(), $form->getName()];
                $formOutputWorkflows[$form->getId()] = array_map(static function (OutputWorkflowInterface $outputWorkflow) {
                    return [$outputWorkflow->getId(), $outputWorkflow->getName()];
                }, $form->getOutputWorkflows()->toArray());
            }
        }

        if ($info instanceof Document\Editable\Area\Info) {

            /** @var Document\Editable\Select $formNameElement */
            $formNameElement = $this->getDocumentEditable($info->getDocument(), 'select', 'formName');
            if (!$formNameElement->isEmpty()) {
                $formId = (int) $formNameElement->getData();
            }

            /** @var Document\Editable\Select $outputWorkflowSelection */
            $outputWorkflowSelection = $this->getDocumentEditable($info->getDocument(), 'select', 'outputWorkflow');
        }

        $preSelectedOutputWorkflow = 'none';
        $formOutputWorkflowStore = [['none', $this->translator->trans('form_builder.area.no_output_workflow', [], 'admin')]];

        if ($formId !== null && isset($formOutputWorkflows[$formId]) && count($formOutputWorkflows[$formId]) > 0) {
            $formOutputWorkflowStore = [];
            foreach ($formOutputWorkflows[$formId] as $index => $outputWorkflow) {
                if ($index === 0) {
                    $preSelectedOutputWorkflow = $outputWorkflow[0];
                }

                $validWorkflowIdsForCurrentSelection[] = $outputWorkflow[0];
                $formOutputWorkflowStore[] = [$outputWorkflow[0], $outputWorkflow[1]];
            }
        }

        if (($outputWorkflowSelection instanceof Document\Editable\Select) && $outputWorkflowSelection->isEmpty() === false) {
            $currentSelection = $outputWorkflowSelection->getData();
            if (is_numeric($currentSelection) && !in_array((int) $currentSelection, $validWorkflowIdsForCurrentSelection, true)) {
                $outputWorkflowSelection->setDataFromResource($preSelectedOutputWorkflow);
            }
        }

        $tabbedItems = [];

        $tabbedItems[] = [
            'type'     => 'panel',
            'title'    => $this->translator->trans('form_builder.area.tab.form', [], 'admin'),
            'defaults' => [
                'cls' => 'form-builder-panel'
            ],
            'items'    => [
                [
                    'type'   => 'select',
                    'name'   => 'formName',
                    'label'  => $this->translator->trans('form_builder.area.form', [], 'admin'),
                    'config' => [
                        'store'    => $availableForms,
                        'width'    => 250,
                        'onchange' => 'formBuilderAreaWatcher.watchOutputWorkflow.bind(this)'
                    ]
                ],
                [
                    'type'   => 'select',
                    'name'   => 'outputWorkflow',
                    'label'  => $this->translator->trans('form_builder.area.output_workflow', [], 'admin'),
                    'config' => [
                        'defaultValue' => $preSelectedOutputWorkflow,
                        'width'        => 250,
                        'store'        => $formOutputWorkflowStore,
                        'class'        => 'fb-output-workflow-selector',
                    ]
                ]
            ]
        ];

        $tabbedItems = $this->addTemplateTab($tabbedItems);
        $tabbedItems = $this->addPresetTab($tabbedItems, $baseDocument);

        $editableDialog->setReloadOnClose(true);
        $editableDialog->setWidth(600);
        $editableDialog->setHeight(450);

        $editableDialog->setItems([
            'type'  => 'tabpanel',
            'items' => $tabbedItems
        ]);

        return $editableDialog;
    }

    protected function addTemplateTab(array $tabbedItems): array
    {
        $formTemplateStore = [];
        foreach ($this->templateManager->getFormTemplates(true) as $template) {
            $template[1] = $this->translator->trans($template[1], [], 'admin');
            $formTemplateStore[] = $template;
        }

        $tabbedItems[] = [
            'type'  => 'panel',
            'title' => $this->translator->trans('form_builder.area.tab.template', [], 'admin'),
            'items' => [
                [
                    'type'   => 'select',
                    'name'   => 'formType',
                    'label'  => $this->translator->trans('form_builder.area.form_template', [], 'admin'),
                    'config' => [
                        'defaultValue' => $this->templateManager->getDefaultFormTemplate(),
                        'width'        => 250,
                        'store'        => $formTemplateStore
                    ]
                ]
            ]
        ];

        return $tabbedItems;
    }

    protected function addPresetTab(array $tabbedItems, Document\PageSnippet $baseDocument): array
    {
        $formPresetsStore = [];

        $formPresets = $this->presetManager->getAll($baseDocument);

        if (empty($formPresets)) {
            return $tabbedItems;
        }

        $formPresetsStore[] = ['custom', $this->translator->trans('form_builder.area.no_form_preset', [], 'admin')];

        foreach ($formPresets as $presetName => $preset) {
            $formPresetsStore[] = [$presetName, $preset['nice_name']];
        }

        $tabbedItems[] = [
            'type'  => 'panel',
            'title' => $this->translator->trans('form_builder.area.tab.preset', [], 'admin'),
            'items' => [
                [
                    'type'   => 'select',
                    'name'   => 'formPreset',
                    'label'  => $this->translator->trans('form_builder.area.form_preset', [], 'admin'),
                    'config' => [
                        'defaultValue' => 'custom',
                        'width'        => 250,
                        'store'        => $formPresetsStore,
                        'onchange'     => 'formBuilderAreaWatcher.watchPresets.bind(this)'
                    ]
                ]
            ]
        ];

        return $tabbedItems;
    }

    public function getName(): string
    {
        return 'Form';
    }

    public function getHtmlTagOpen(Document\Editable\Area\Info $info): string
    {
        return '';
    }

    public function getHtmlTagClose(Document\Editable\Area\Info $info): string
    {
        return '';
    }

    public function getIcon(): string
    {
        return '/bundles/formbuilder/img/application_form.svg';
    }

    public function getTemplate(): string
    {
        return sprintf('@FormBuilder/form/form.%s', $this->getTemplateSuffix());
    }

    public function getTemplateLocation(): string
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }

    public function getTemplateSuffix(): string
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }
}
