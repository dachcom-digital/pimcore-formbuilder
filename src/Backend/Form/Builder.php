<?php

namespace FormBuilderBundle\Backend\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\FormTypeInterface;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Registry\FormTypeRegistry;
use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormField;

class Builder
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var FormTypeRegistry
     */
    protected $formTypeRegistry;

    /**
     * @var FormTypeRegistry
     */
    protected $templateManager;

    /**
     * Builder constructor.
     *
     * @param Configuration    $configuration
     * @param FormTypeRegistry $formTypeRegistry
     * @param TemplateManager $templateManager
     */
    public function __construct(Configuration $configuration, FormTypeRegistry $formTypeRegistry, TemplateManager $templateManager)
    {
        $this->configuration = $configuration;
        $this->formTypeRegistry = $formTypeRegistry;
        $this->templateManager = $templateManager;
    }

    /**
     * Generate array form with form attributes and available form types structure.
     * @param Form $form
     *
     * @return array
     */
    public function generateExtJsForm(Form $form)
    {
        $data = [
            'id'     => $form->getId(),
            'name'   => $form->getName(),
            'date'   => $form->getDate(),
            'config' => $form->getConfig(),
        ];

        /** @var FormField $field */
        foreach ($form->getFields() as $field) {
            $fieldData = $field->toArray();
            $fieldDataOptions = $fieldData['options'];

            unset($fieldData['options']);

            //flatten form options array.
            foreach ($fieldDataOptions as $optionKey => $optionName) {
                $fieldData[$optionKey] = $optionName;
            }

            $data['fields'][] = $fieldData;
        }

        $data['fields_structure'] = $this->generateExtJsFormTypesStructure();
        $data['fields_template'] = $this->getFormTypeTemplates();

        return $data;
    }

    private function generateExtJsFormTypesStructure()
    {
        $formTypes = $this->formTypeRegistry->getAll();

        $fieldStructure = $this->getFieldTypeGroups();

        /** @var FormTypeInterface $formTypeElement */
        foreach ($formTypes as $formTypeElement) {

            $formType = $formTypeElement->getType();

            $fieldStructureElement = [
                'type'                 => $formType,
                'text'                 => $formTypeElement->getTitle(),
                'icon_class'           => $this->getFormTypeIcon($formType),
                'configuration_layout' => $this->getFormTypeBackendConfiguration($formType)
            ];

            $groupIndex = array_search($this->getFormTypeGroup($formType), array_column($fieldStructure, 'id'));

            if ($groupIndex !== FALSE) {
                $fieldStructure[$groupIndex]['fields'][] = $fieldStructureElement;
            } else {
                $groupIndex = array_search('other_fields', array_column($fieldStructure, 'id'));
                $fieldStructure[$groupIndex]['fields'][] = $fieldStructureElement;
            }
        }

        return $fieldStructure;
    }

    private function getFieldTypeGroups()
    {
        $groups = $this->configuration->getBackendConfig('backend_base_field_type_groups');

        foreach ($groups as &$group) {
            $group['fields'] = [];
        }

        return $groups;
    }

    private function getFormTypeBackendConfiguration($formType)
    {
        $baseConfig = $this->configuration->getBackendConfig('backend_base_field_type_config');
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');

        $formTypeConfig = $formConfig[$formType];

        $tabs = array_merge($baseConfig['tabs'], $formTypeConfig['tabs']);
        $displayGroups = array_merge($baseConfig['display_groups'], $formTypeConfig['display_groups']);
        $fields = array_merge($baseConfig['fields'], $formTypeConfig['fields']);

        $data = [];

        foreach ($tabs as $tab) {
            $tabData = $tab;
            $tabData['fields'] = [];
            $data[] = $tabData;
        }

        foreach ($displayGroups as $displayGroup) {

            $displayGroupData = $displayGroup;
            $displayGroupData['fields'] = [];

            foreach ($data as &$tabRow) {
                if ($tabRow['id'] === $displayGroup['tab_id']) {
                    unset($displayGroupData['tab_id']);
                    $tabRow['fields'][] = $displayGroupData;
                    break;
                }
            }
        }

        foreach ($fields as $field) {

            foreach ($data as &$tabRow) {
                foreach ($tabRow['fields'] as &$displayGroupRow) {
                    if ($displayGroupRow['id'] === $field['display_group_id']) {
                        unset($field['display_group_id']);
                        $displayGroupRow['fields'][] = $field;
                        break;
                    }
                }
            }
        }

        return $data;
    }

    private function getFormTypeGroup($formType)
    {
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');
        return $formConfig[$formType]['form_type_group'];
    }

    private function getFormTypeIcon($formType)
    {
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');
        return $formConfig[$formType]['icon_class'];
    }

    private function getFormTypeTemplates()
    {
        return $this->templateManager->getFieldTemplates();
    }
}