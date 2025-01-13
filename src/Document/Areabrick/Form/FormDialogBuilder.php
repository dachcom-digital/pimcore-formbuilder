<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Document\Areabrick\Form;

use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Manager\PresetManager;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Model\Document;
use Pimcore\Model\Document\PageSnippet;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormDialogBuilder
{
    public function __construct(
        protected FormDefinitionManager $formDefinitionManager,
        protected PresetManager $presetManager,
        protected TemplateManager $templateManager,
        protected TranslatorInterface $translator
    ) {
    }

    public function build(array $options): EditableDialogBoxConfiguration
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'add_preset_tab'                    => true,
            'add_template_tab'                  => true,
            'document'                          => null,
            'form_selector_editable'            => null,
            'output_workflow_selector_editable' => null,
            'reload_on_close'                   => true,
            'width'                             => 600,
            'height'                            => 450
        ]);

        $optionsResolver->setAllowedTypes('document', ['null', PageSnippet::class]);
        $optionsResolver->setAllowedTypes('form_selector_editable', ['null', Document\Editable\Select::class]);
        $optionsResolver->setAllowedTypes('output_workflow_selector_editable', ['null', Document\Editable\Select::class]);
        $optionsResolver->setAllowedTypes('add_preset_tab', ['bool']);
        $optionsResolver->setAllowedTypes('add_template_tab', ['bool']);
        $optionsResolver->setAllowedTypes('reload_on_close', ['bool']);
        $optionsResolver->setAllowedTypes('width', ['integer']);
        $optionsResolver->setAllowedTypes('height', ['integer']);

        $options = $optionsResolver->resolve($options);

        $editableDialog = new EditableDialogBoxConfiguration();

        $formId = null;
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

        $formSelectorEditable = $options['form_selector_editable'];
        $outputWorkflowSelectorEditable = $options['output_workflow_selector_editable'];

        if ($formSelectorEditable instanceof Document\Editable\Select && !$formSelectorEditable->isEmpty()) {
            $formId = (int) $formSelectorEditable->getData();
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

        if (($outputWorkflowSelectorEditable instanceof Document\Editable\Select) && $outputWorkflowSelectorEditable->isEmpty() === false) {
            $currentSelection = $outputWorkflowSelectorEditable->getData();
            if (is_numeric($currentSelection) && !in_array((int) $currentSelection, $validWorkflowIdsForCurrentSelection, true)) {
                $outputWorkflowSelectorEditable->setDataFromResource($preSelectedOutputWorkflow);
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

        if ($options['add_template_tab'] === true) {
            $tabbedItems = $this->addTemplateTab($tabbedItems);
        }

        if ($options['add_preset_tab'] === true) {
            $tabbedItems = $this->addPresetTab($tabbedItems, $options['document']);
        }

        $editableDialog->setReloadOnClose($options['reload_on_close']);
        $editableDialog->setWidth($options['width']);
        $editableDialog->setHeight($options['height']);

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
}
