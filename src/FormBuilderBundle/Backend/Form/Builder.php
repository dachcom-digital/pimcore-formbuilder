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
     * @var TemplateManager
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
     * @throws \Exception
     */
    public function generateExtJsForm(FormInterface $form)
    {
        $data = [
            'id'     => $form->getId(),
            'name'   => $form->getName(),
            'group'  => $form->getGroup(),
            'config' => $form->getConfig(),
            'meta'   => [
                'creation_date'     => $form->getCreationDate(),
                'modification_date' => $form->getModificationDate(),
                'created_by'        => $form->getCreatedBy(),
                'modified_by'       => $form->getModifiedBy(),
            ]
        ];

        $fieldData = [];
        /** @var FormFieldInterface $field */
        foreach ($form->getFields() as $field) {
            $fieldData[] = $field->toArray();
        }

        $data['fields'] = $this->generateExtJsFields($fieldData);
        $data['fields_structure'] = $this->generateExtJsFormTypesStructure();
        $data['fields_template'] = $this->getFormTypeTemplates();
        $data['config_store'] = $this->getFormStoreData();
        $data['validation_constraints'] = $this->getTranslatedValidationConstraints();
        $data['conditional_logic'] = $this->generateConditionalLogicExtJsFields($form->getConditionalLogic());
        $data['conditional_logic_store'] = $this->generateConditionalLogicStore();

        return $data;
    }

    /**
     * @param array $fields
     *
     * @return array
     * @throws \Exception
     */
    public function generateExtJsFields(array $fields)
    {
        foreach ($fields as &$fieldData) {
            $this->transformOptions($fieldData, true);
        }

        return $fields;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function generateStoreFields(array $data)
    {
        if (!isset($data['fields'])) {
            return $data;
        }

        foreach ($data['fields'] as &$fieldData) {
            $this->transformOptions($fieldData);
        }

        return $data;
    }

    /**
     * @param array $conditionalData
     *
     * @return array
     * @throws \Exception
     */
    public function generateConditionalLogicStoreFields(array $conditionalData)
    {
        if (!empty($conditionalData)) {
            foreach ($conditionalData as &$conditionalDataBlock) {
                foreach (['condition', 'action'] as $conditionalType) {
                    if (isset($conditionalDataBlock[$conditionalType]) && is_array($conditionalDataBlock[$conditionalType])) {
                        foreach ($conditionalDataBlock[$conditionalType] as &$action) {
                            $this->transformConditionalOptions($action, $conditionalType);
                        }
                    }
                }
            }
        }

        return $conditionalData;
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
     *
     * @return array
     * @throws \Exception
     */
    private function generateConditionalLogicExtJsFields($conditionalData)
    {
        if (!empty($conditionalData)) {
            foreach ($conditionalData as &$conditionalDataBlock) {
                foreach (['condition', 'action'] as $conditionalType) {
                    if (isset($conditionalDataBlock[$conditionalType]) && is_array($conditionalDataBlock[$conditionalType])) {
                        foreach ($conditionalDataBlock[$conditionalType] as &$action) {
                            $this->transformConditionalOptions($action, $conditionalType, true);
                        }
                    }
                }
            }
        }

        return $conditionalData;
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

    /**
     * @param $formType
     * @param $formTypeBackendConfig
     *
     * @return array
     */
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
        $constraints = [];
        foreach ($this->configuration->getAvailableConstraints() as $constraintId => $constraintData) {
            $constraints[] = $constraintId;
        }
        // all constraints are allowed
        if (!isset($formTypeBackendConfig['constraints'])) {
            return $constraints;
        }

        $definedConstraints = $formTypeBackendConfig['constraints'];

        // no constraints are allowed
        if (isset($definedConstraints['enabled'])
            && is_array($definedConstraints['enabled']) && count($definedConstraints['enabled']) === 0) {
            return [];
        }

        // specific constraints enabled
        if (isset($definedConstraints['enabled']) && is_array($definedConstraints['enabled'])) {
            // only get available constraints
            return array_values(array_intersect($constraints, $definedConstraints['enabled']));
        }

        // specific constraints disabled
        if (isset($definedConstraints['disabled']) && is_array($definedConstraints['disabled'])) {
            return array_values(array_diff($constraints, $definedConstraints['disabled']));
        }

        return [];
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
     * @return array
     */
    private function getFormStoreData()
    {
        $formAttributes = $this->configuration->getConfig('form_attributes');

        return [
            'attributes' => $formAttributes
        ];
    }

    /**
     * @param string $formType
     *
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
     * @param array $fieldData
     * @param bool  $reverse
     *
     * @return mixed
     * @throws \Exception
     */
    private function transformOptions(&$fieldData, $reverse = false)
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
                $transformer = $this->optionsTransformerRegistry->get($optionConfig['options_transformer']);

                if ($reverse === false) {
                    $fieldData['options'][$optionName] = $transformer->transform($optionValue, $optionConfig);
                } else {
                    $fieldData['options'][$optionName] = $transformer->reverseTransform($optionValue, $optionConfig);
                }
            }
        }
    }

    /**
     * @param array  $fieldData
     * @param string $type
     * @param bool   $reverse
     *
     * @throws \Exception
     */
    private function transformConditionalOptions(&$fieldData, $type, $reverse = false)
    {
        $baseConfig = $this->configuration->getBackendConditionalLogicConfig();
        $typeConfig = $baseConfig[$type];

        $elementConfig = $typeConfig[$fieldData['type']];
        $elementFormConfig = $elementConfig['form'];

        foreach ($elementFormConfig as $formElementName => $config) {
            if (!isset($fieldData[$formElementName])) {
                continue;
            }

            $transformer = null;
            $fieldType = $config['type'];
            $fieldDataValue = $fieldData[$formElementName];

            // conditional field transformer
            if ($fieldType === 'conditional_select') {
                $conditionalFieldIdentifier = $config['conditional_identifier'];
                $fieldIndex = $conditionalFieldIdentifier;
                if (isset($fieldData[$conditionalFieldIdentifier])) {
                    foreach ($config['conditional'] as $conditionalFieldName => $conditionalFieldConfig) {
                        if ($fieldDataValue === $conditionalFieldName) {
                            if (!empty($conditionalFieldConfig['options_transformer'])) {
                                $transformer = $this->optionsTransformerRegistry->get($conditionalFieldConfig['options_transformer']);
                            }
                        }
                    }
                }
            } else {
                // default field transformer
                $fieldIndex = $formElementName;
                if (!empty($config['options_transformer'])) {
                    $transformer = $this->optionsTransformerRegistry->get($config['options_transformer']);
                }
            }

            if ($transformer instanceof OptionsTransformerInterface) {
                if ($reverse === false) {
                    $fieldData[$fieldIndex] = $transformer->transform($fieldData[$fieldIndex]);
                } else {
                    $fieldData[$fieldIndex] = $transformer->reverseTransform($fieldData[$fieldIndex]);
                }
            }
        }
    }
}