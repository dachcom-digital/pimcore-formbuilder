<?php

namespace FormBuilderBundle\Backend\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use FormBuilderBundle\Registry\ConditionalLogicRegistry;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Transformer\OptionsTransformerInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Pimcore\Translation\Translator;

class Builder
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var OptionsTransformerRegistry
     */
    protected $templateManager;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var OptionsTransformerRegistry
     */
    protected $optionsTransformerRegistry;
    /**
     * @var ConditionalLogicRegistry
     */
    protected $conditionalLogicRegistry;

    /**
     * Builder constructor.
     *
     * @param Configuration              $configuration
     * @param TemplateManager            $templateManager
     * @param Translator                 $translator
     * @param OptionsTransformerRegistry $optionsTransformerRegistry
     * @param ConditionalLogicRegistry   $conditionalLogicRegistry
     */
    public function __construct(
        Configuration $configuration,
        TemplateManager $templateManager,
        Translator $translator,
        OptionsTransformerRegistry $optionsTransformerRegistry,
        ConditionalLogicRegistry $conditionalLogicRegistry
    ) {
        $this->configuration = $configuration;
        $this->templateManager = $templateManager;
        $this->translator = $translator;
        $this->optionsTransformerRegistry = $optionsTransformerRegistry;
        $this->conditionalLogicRegistry = $conditionalLogicRegistry;
    }

    /**
     * Generate array form with form attributes and available form types structure.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    public function generateExtJsForm(FormInterface $form)
    {
        $data = [
            'id'     => $form->getId(),
            'name'   => $form->getName(),
            'date'   => $form->getDate(),
            'config' => $form->getConfig(),
        ];

        $fieldData = [];
        /** @var FormFieldInterface $field */
        foreach ($form->getFields() as $field) {
            $fieldData[] = $field->toArray();
        }

        $data['fields'] = $this->generateExtJsFields($fieldData);
        $data['fields_structure'] = $this->generateExtJsFormTypesStructure();
        $data['fields_template'] = $this->getFormTypeTemplates();
        $data['validation_constraints'] = $this->getTranslatedValidationConstraints();
        $data['conditional_logic'] = $this->generateConditionalLogicStructure($form->getConditionalLogic());
        $data['conditional_logic_store'] = $this->generateConditionalLogicStore();

        return $data;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function generateExtJsFields(array $fields)
    {
        $formFields = [];
        foreach ($fields as $field) {
            $formFields[] = $this->transformOptions($field, true);
        }

        return $formFields;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function generateStoreFields(array $data)
    {
        foreach ($data['fields'] as &$fieldData) {
            $fieldData = $this->transformOptions($fieldData);
        }

        return $data;
    }

    /**
     * @return array
     */
    private function generateExtJsFormTypesStructure()
    {
        $formTypes = $this->configuration->getConfig('types');
        $fieldStructure = $this->getFieldTypeGroups();

        foreach ($formTypes as $formType => $formTypeConfiguration) {

            if (!$this->isAllowedFormType($formType)) {
                continue;
            }

            $beConfig = $formTypeConfiguration['backend'];
            $fieldStructureElement = [
                'type'                 => $formType,
                'label'                => $this->getFormTypeLabel($formType, $beConfig),
                'icon_class'           => $this->getFormTypeIcon($formType, $beConfig),
                'constraints'          => $this->getFormTypeAllowedConstraints($formType, $beConfig),
                'configuration_layout' => $this->getFormTypeBackendConfiguration($formType, $beConfig)
            ];

            $groupIndex = array_search($this->getFormTypeGroup($formType, $beConfig), array_column($fieldStructure, 'id'));

            if ($groupIndex !== false) {
                $fieldStructure[$groupIndex]['fields'][] = $fieldStructureElement;
            } else {
                $groupIndex = array_search('other_fields', array_column($fieldStructure, 'id'));
                $fieldStructure[$groupIndex]['fields'][] = $fieldStructureElement;
            }
        }

        return $fieldStructure;
    }

    /**
     * @param $conditionalData
     * @return array
     */
    private function generateConditionalLogicStructure($conditionalData)
    {
        $formConditionalLogicData = [];
        if (!empty($conditionalData)) {
            $formConditionalLogicData['cl'] = $conditionalData;
        }

        return $formConditionalLogicData;
    }

    /**
     * @return array
     */
    private function generateConditionalLogicStore()
    {
        $actions = [];
        foreach ($this->conditionalLogicRegistry->getAllConfiguration('action') as $actionName => $action) {
            $actions[] = [
                'identifier' => $actionName,
                'name'       => $action['name'],
                'icon'       => $action['icon'],
            ];
        }

        $conditions = [];
        foreach ($this->conditionalLogicRegistry->getAllConfiguration('condition') as $conditionName => $condition) {
            $conditions[] = [
                'identifier' => $conditionName,
                'name'       => $condition['name'],
                'icon'       => $condition['icon'],
            ];
        }

        $formConditionalLogicData = [
            'actions'    => $actions,
            'conditions' => $conditions,
        ];

        return $formConditionalLogicData;
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
            $group['label'] = $this->translate($group['label']);
            $group['fields'] = [];
            $groupData[] = $group;
        }

        return $groupData;
    }

    /**
     * @return array
     */
    private function getTranslatedValidationConstraints()
    {
        $constraints = $this->configuration->getAvailableConstraints();

        $constraintData = [];
        foreach ($constraints as $constraintId => &$constraint) {
            $constraint['label'] = $this->translate($constraint['label']);
            $constraintData[] = $constraint;
        }

        return $constraintData;
    }

    /**
     * @param $formType
     * @param $formTypeBackendConfig
     *
     * @return array
     */
    private function getFormTypeBackendConfiguration($formType, $formTypeBackendConfig)
    {
        $fieldConfigFields = $this->getMergedFormTypeConfig($formType, $formTypeBackendConfig);

        $data = [];

        foreach ($fieldConfigFields['tabs'] as $tabId => $tab) {
            $tabData = $tab;
            $tabData['id'] = $tabId;
            $tabData['fields'] = [];
            $data[] = $tabData;
        }

        foreach ($fieldConfigFields['displayGroups'] as $displayGroupId => $displayGroup) {

            $displayGroupData = $displayGroup;
            $displayGroupData['id'] = $displayGroupId;
            $displayGroupData['label'] = $this->translate($displayGroupData['label']);
            $displayGroupData['fields'] = [];

            foreach ($data as &$tabRow) {
                if ($tabRow['id'] === $displayGroup['tab_id']) {
                    unset($displayGroupData['tab_id']);
                    $tabRow['fields'][] = $displayGroupData;
                    break;
                }
            }
        }

        foreach ($fieldConfigFields['fields'] as $fieldId => $field) {

            if ($field === false) {
                continue;
            }

            $fieldData = $field;
            $fieldData['id'] = $fieldId;
            $fieldData['label'] = $this->translate($fieldData['label']);

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

    private function getMergedFormTypeConfig($formType, $formTypeBackendConfig)
    {
        $baseConfig = $this->configuration->getBackendConfig('backend_base_field_type_config');

        if (is_null($formTypeBackendConfig)) {
            throw new InvalidConfigurationException(sprintf('No valid form field configuration for "%s" found.', $formType));
        }

        $tabs = array_merge($baseConfig['tabs'], $formTypeBackendConfig['tabs']);
        $displayGroups = array_merge($baseConfig['display_groups'], $formTypeBackendConfig['display_groups']);
        $fields = array_merge($baseConfig['fields'], $formTypeBackendConfig['fields']);

        return ['tabs' => $tabs, 'displayGroups' => $displayGroups, 'fields' => $fields];
    }

    /**
     * @param $formType
     * @param $formTypeBackendConfig
     *
     * @return mixed
     */
    private function getFormTypeGroup($formType, $formTypeBackendConfig)
    {
        return $formTypeBackendConfig['form_type_group'];
    }

    /**
     * @param $formType
     * @param $formTypeBackendConfig
     *
     * @return mixed
     */
    private function getFormTypeIcon($formType, $formTypeBackendConfig)
    {
        return $formTypeBackendConfig['icon_class'];
    }

    /**
     * @param $formType
     * @param $formTypeBackendConfig
     *
     * @return mixed
     */
    private function getFormTypeAllowedConstraints($formType, $formTypeBackendConfig)
    {
        return $formTypeBackendConfig['constraints'];
    }

    /**
     * @param $formType
     * @param $formTypeBackendConfig
     *
     * @return string
     */
    private function getFormTypeLabel($formType, $formTypeBackendConfig)
    {
        return $this->translate($formTypeBackendConfig['label']);
    }

    /**
     * Get translated Form Type Templates
     *
     * @return array
     */
    private function getFormTypeTemplates()
    {
        $templates = $this->templateManager->getFieldTemplates();
        $typeTemplates = [];
        foreach ($templates as $template) {
            $template['label'] = $this->translate($template['label']);
            $typeTemplates[] = $template;
        }

        return $typeTemplates;
    }

    /**
     * @param string $formType
     * @return bool
     */
    private function isAllowedFormType($formType = null)
    {
        $adminSettings = $this->configuration->getConfig('admin');
        $activeFields = $adminSettings['active_elements']['fields'];
        $inactiveFields = $adminSettings['inactive_elements']['fields'];

        if (empty($activeFields) && empty($inactiveFields)) {
            return true;
        }

        if (!empty($inactiveFields) && in_array($formType, $inactiveFields)) {
            return false;
        }

        if (!empty($activeFields) && !in_array($formType, $activeFields)) {
            return false;
        }

        return true;
    }

    /**
     * @param $value
     *
     * @return string
     */
    private function translate($value)
    {
        if (empty($value)) {
            return $value;
        }

        return $this->translator->trans($value, [], 'admin');
    }

    /**
     * @param      $fieldData
     * @param bool $reverse
     *
     * @return mixed
     */
    private function transformOptions($fieldData, $reverse = false)
    {
        $formTypes = $this->configuration->getConfig('types');

        if (!isset($fieldData['options']) || !is_array($fieldData['options'])) {
            return $fieldData;
        }

        $formTypeConfig = $formTypes[$fieldData['type']];
        $backendConfig = $this->getMergedFormTypeConfig($fieldData['type'], $formTypeConfig['backend']);

        if (!isset($backendConfig['fields'])) {
            return $fieldData;
        }

        foreach ($fieldData['options'] as $optionName => $optionValue) {

            if (!isset($backendConfig['fields']['options.' . $optionName])) {
                continue;
            }

            $optionConfig = $backendConfig['fields']['options.' . $optionName];
            if (!empty($optionConfig['options_transformer'])) {

                /** @var OptionsTransformerInterface $transformer */
                $transformer = $fieldData['options'][$optionName] = $this->optionsTransformerRegistry
                    ->get($optionConfig['options_transformer']);

                if ($reverse === false) {
                    $fieldData['options'][$optionName] = $transformer->transform($optionValue, $optionConfig);
                } else {
                    $fieldData['options'][$optionName] = $transformer->reverseTransform($optionValue, $optionConfig);
                }
            }
        }

        return $fieldData;
    }
}