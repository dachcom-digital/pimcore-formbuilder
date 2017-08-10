<?php

namespace FormBuilderBundle\Backend\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\FormTypeInterface;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Registry\FormTypeRegistry;
use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormField;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Pimcore\Translation\Translator;

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
     * @var Translator
     */
    protected $translator;

    /**
     * Builder constructor.
     *
     * @param Configuration    $configuration
     * @param FormTypeRegistry $formTypeRegistry
     * @param TemplateManager $templateManager
     * @param TemplateManager $translator
     */
    public function __construct(
        Configuration $configuration,
        FormTypeRegistry $formTypeRegistry,
        TemplateManager $templateManager,
        Translator $translator
    )
    {
        $this->configuration = $configuration;
        $this->formTypeRegistry = $formTypeRegistry;
        $this->templateManager = $templateManager;
        $this->translator = $translator;
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

    /**
     * @return array
     */
    private function generateExtJsFormTypesStructure()
    {
        $formTypes = $this->formTypeRegistry->getAll();

        $fieldStructure = $this->getFieldTypeGroups();

        /** @var FormTypeInterface $formTypeElement */
        foreach ($formTypes as $formTypeElement) {

            $formType = $formTypeElement->getType();

            $fieldStructureElement = [
                'type'                 => $formType,
                'label'                => $this->getFormTypeLabel($formType),
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

    /**
     * @return array
     */
    private function getFieldTypeGroups()
    {
        $groups = $this->configuration->getBackendConfig('backend_base_field_type_groups');

        $groupData = [];
        foreach ($groups as $groupId => &$group) {
            $group['id'] = $groupId;
            $group['label'] = $this->translator->trans($group['label'], [], 'admin');
            $group['fields'] = [];
            $groupData[] = $group;
        }

        return $groupData;
    }

    /**
     * @param $formType
     *
     * @return array
     */
    private function getFormTypeBackendConfiguration($formType)
    {
        $baseConfig = $this->configuration->getBackendConfig('backend_base_field_type_config');
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');

        $formTypeConfig = $formConfig[$formType];

        if(is_null($formTypeConfig)) {
            throw new InvalidConfigurationException(sprintf('No valid form field configuration for "%s" found.', $formType));
        }

        $tabs = array_merge($baseConfig['tabs'], $formTypeConfig['tabs']);
        $displayGroups = array_merge($baseConfig['display_groups'], $formTypeConfig['display_groups']);
        $fields = array_merge($baseConfig['fields'], $formTypeConfig['fields']);

        $data = [];

        foreach ($tabs as $tabId => $tab) {
            $tabData = $tab;
            $tabData['id'] = $tabId;
            $tabData['fields'] = [];
            $data[] = $tabData;
        }

        foreach ($displayGroups as $displayGroupId => $displayGroup) {

            $displayGroupData = $displayGroup;
            $displayGroupData['id'] = $displayGroupId;
            $displayGroupData['label'] = $this->translator->trans($displayGroupData['label'], [], 'admin');
            $displayGroupData['fields'] = [];

            foreach ($data as &$tabRow) {
                if ($tabRow['id'] === $displayGroup['tab_id']) {
                    unset($displayGroupData['tab_id']);
                    $tabRow['fields'][] = $displayGroupData;
                    break;
                }
            }
        }

        foreach ($fields as $fieldId => $field) {

            if($field === FALSE) {
                continue;
            }

            $fieldData = $field;
            $fieldData['id'] = $fieldId;
            $fieldData['label'] = $this->translator->trans($fieldData['label'], [], 'admin');
            unset($fieldData['display_group_id']);

            foreach ($data as &$tabRow) {
                foreach ($tabRow['fields'] as &$displayGroupRow) {
                    if ($displayGroupRow['id'] === $field['display_group_id']) {
                        $displayGroupRow['fields'][] = $fieldData;
                        break;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $formType
     *
     * @return mixed
     */
    private function getFormTypeGroup($formType)
    {
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');
        return $formConfig[$formType]['form_type_group'];
    }

    /**
     * @param $formType
     *
     * @return mixed
     */
    private function getFormTypeIcon($formType)
    {
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');
        return $formConfig[$formType]['icon_class'];
    }

    /**
     * @param $formType
     *
     * @return mixed
     */
    private function getFormTypeLabel($formType)
    {
        $formConfig = $this->configuration->getBackendConfig('backend_field_type_config');
        return $this->translator->trans($formConfig[$formType]['label'], [], 'admin');
    }

    /**
     * Get translated Form Type Templates
     * @return array
     */
    private function getFormTypeTemplates()
    {
        $templates = $this->templateManager->getFieldTemplates();
        $typeTemplates = [];
        foreach($templates as $template) {
            $template['label'] = $this->translator->trans($template['label'], [], 'admin');
            $typeTemplates[] = $template;
        }

        return $typeTemplates;
    }
}