<?php

namespace FormBuilderBundle\Builder;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Manager\TemplateManager;
use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use FormBuilderBundle\Registry\ConditionalLogicRegistry;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use FormBuilderBundle\Transformer\OptionsTransformerInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Pimcore\Translation\Translator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ExtJsFormBuilder
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

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
     * @var OutputWorkflowChannelRegistry
     */
    protected $outputWorkflowChannelRegistry;

    /**
     * @param Configuration                 $configuration
     * @param SerializerInterface           $serializer
     * @param TemplateManager               $templateManager
     * @param Translator                    $translator
     * @param OptionsTransformerRegistry    $optionsTransformerRegistry
     * @param ConditionalLogicRegistry      $conditionalLogicRegistry
     * @param OutputWorkflowChannelRegistry $outputWorkflowChannelRegistry
     */
    public function __construct(
        Configuration $configuration,
        SerializerInterface $serializer,
        TemplateManager $templateManager,
        Translator $translator,
        OptionsTransformerRegistry $optionsTransformerRegistry,
        ConditionalLogicRegistry $conditionalLogicRegistry,
        OutputWorkflowChannelRegistry $outputWorkflowChannelRegistry
    ) {
        $this->configuration = $configuration;
        $this->serializer = $serializer;
        $this->templateManager = $templateManager;
        $this->translator = $translator;
        $this->optionsTransformerRegistry = $optionsTransformerRegistry;
        $this->conditionalLogicRegistry = $conditionalLogicRegistry;
        $this->outputWorkflowChannelRegistry = $outputWorkflowChannelRegistry;
    }

    /**
     * Generate array form with form attributes and available form types structure.
     *
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     *
     * @throws \Exception
     */
    public function generateExtJsForm(FormDefinitionInterface $formDefinition)
    {
        $data = [
            'id'                   => $formDefinition->getId(),
            'name'                 => $formDefinition->getName(),
            'group'                => $formDefinition->getGroup(),
            'config'               => $formDefinition->getConfig(),
            'has_output_workflows' => $formDefinition->hasOutputWorkflows(),
            'meta'                 => [
                'creation_date'     => $formDefinition->getCreationDate(),
                'modification_date' => $formDefinition->getModificationDate(),
                'created_by'        => $formDefinition->getCreatedBy(),
                'modified_by'       => $formDefinition->getModifiedBy()
            ]
        ];

        $fieldData = [];
        foreach ($formDefinition->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $fieldData[] = $field->toArray();
            }
        }

        $data['sensitive_field_names'] = $this->getSensitiveFormFieldNames($formDefinition);
        $data['fields'] = $this->generateExtJsFields($fieldData);
        $data['fields_structure'] = $this->generateExtJsFormTypesStructure();
        $data['fields_template'] = $this->getFormTypeTemplates();
        $data['config_store'] = $this->getFormStoreData();
        $data['container_types'] = $this->getTranslatedContainerTypes();
        $data['validation_constraints'] = $this->getTranslatedValidationConstraints();
        $data['conditional_logic'] = $this->generateConditionalLogicExtJsFields($formDefinition->getConditionalLogic());
        $data['conditional_logic_store'] = $this->generateConditionalLogicStore();

        return $data;
    }

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     * @throws \Exception
     */
    public function generateExtJsFormFields(FormDefinitionInterface $formDefinition)
    {
        $data = [];
        $fieldData = [];
        foreach ($formDefinition->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $fieldData[] = $field->toArray();
            }
        }

        $fields = $this->generateExtJsFields($fieldData);
        $fieldsTypesData = $this->generateExtJsFormTypesStructure(true);
        $containerTypes = $this->getTranslatedContainerTypes();

        foreach ($fields as $field) {

            $fieldType = $field['type'];

            if ($fieldType === 'container') {
                $fieldDataIndex = array_search($field['sub_type'], array_column($containerTypes, 'id'));
                $typeData = $fieldDataIndex !== false ? $containerTypes[$fieldDataIndex] : [];

                if (isset($field['fields']) && is_array($field['fields'])) {
                    $subData = [];
                    foreach ($field['fields'] as $subField) {
                        $subFieldType = $subField['type'];
                        $subFieldDataIndex = array_search($subFieldType, array_column($fieldsTypesData, 'type'));
                        $subTypeData = $subFieldDataIndex !== false ? $fieldsTypesData[$subFieldDataIndex] : [];
                        $subData[] = [
                            'data' => $subField,
                            'type' => $subTypeData
                        ];
                    }

                    $field['fields'] = $subData;
                }
            } else {
                $fieldDataIndex = array_search($fieldType, array_column($fieldsTypesData, 'type'));
                $typeData = $fieldDataIndex !== false ? $fieldsTypesData[$fieldDataIndex] : [];
            }

            $data[] = [
                'data' => $field,
                'type' => $typeData
            ];
        }

        return $data;
    }

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     *
     * @return array
     *
     * @throws \Throwable
     */
    public function generateExtJsOutputWorkflowForm(OutputWorkflowInterface $outputWorkflow)
    {
        $data = [
            'id'   => $outputWorkflow->getId(),
            'name' => $outputWorkflow->getName(),
            'meta' => []
        ];

        $outputWorkflowChannels = $this->serializer instanceof NormalizerInterface
            ? $this->serializer->normalize($outputWorkflow->getChannels(), 'array', ['groups' => ['ExtJs']])
            : [];

        $data['output_workflow_channels'] = $outputWorkflowChannels;
        $data['output_workflow_channels_store'] = $this->generateAvailableWorkflowChannelsList();
        $data['output_workflow_success_management'] = $outputWorkflow->getSuccessManagement();

        return $data;
    }

    /**
     * @param array $fields
     *
     * @return array
     *
     * @throws \Exception
     */
    public function generateExtJsFields(array $fields)
    {
        foreach ($fields as &$fieldData) {
            if ($fieldData['type'] === 'container' && is_array($fieldData['fields'])) {
                $this->transformContainerOptions($fieldData, true);
                foreach ($fieldData['fields'] as &$subFieldData) {
                    $this->transformFieldOptions($subFieldData, true);
                }
            } else {
                $this->transformFieldOptions($fieldData, true);
            }
        }

        return $fields;
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Exception
     */
    public function generateStoreFields(array $data)
    {
        if (!isset($data['fields'])) {
            return $data;
        }

        foreach ($data['fields'] as &$fieldData) {
            if ($fieldData['type'] === 'container' && is_array($fieldData['fields'])) {
                $this->transformContainerOptions($fieldData);
                foreach ($fieldData['fields'] as &$subFieldData) {
                    $this->transformFieldOptions($subFieldData);
                }
            } else {
                $this->transformFieldOptions($fieldData);
            }
        }

        return $data;
    }

    /**
     * @param array $conditionalData
     *
     * @return array
     *
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
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     * @throws \Exception
     */
    protected function getSensitiveFormFieldNames(FormDefinitionInterface $formDefinition)
    {
        if (!$formDefinition->hasOutputWorkflows()) {
            return [];
        }

        $fieldNames = [];
        foreach ($formDefinition->getOutputWorkflows() as $outputWorkflow) {

            $workflowFieldNames = [];

            if (!$outputWorkflow->hasChannels()) {
                $fieldNames[$outputWorkflow->getId()] = [];
                continue;
            }

            foreach ($outputWorkflow->getChannels() as $channel) {
                $channelDefinition = $this->outputWorkflowChannelRegistry->get($channel->getType());
                $workflowFieldNames = array_merge($workflowFieldNames, $channelDefinition->getUsedFormFieldNames($channel->getConfiguration()));
            }

            $fieldNames[$outputWorkflow->getId()] = array_unique($workflowFieldNames);
        }

        return $fieldNames;
    }

    /**
     * @return array
     */
    private function generateAvailableWorkflowChannelsList()
    {
        $data = [];
        foreach ($this->outputWorkflowChannelRegistry->getAllIdentifier() as $availableChannel) {
            $data[] = [
                'identifier' => $availableChannel,
                'label'      => $this->translate(sprintf('form_builder.output_workflow.channel.%s', strtolower($availableChannel))),
                'icon_class' => sprintf('form_builder_output_workflow_channel_%s', strtolower($availableChannel))
            ];
        }

        return $data;
    }

    /**
     * @param bool $flat
     *
     * @return array
     */
    private function generateExtJsFormTypesStructure(bool $flat = false)
    {
        $formTypes = $this->configuration->getConfig('types');
        $fieldStructure = $flat === true ? [] : $this->getFieldTypeGroups();

        foreach ($formTypes as $formType => $formTypeConfiguration) {
            if (!$this->isAllowedFormType($formType)) {
                continue;
            }

            $beConfig = $formTypeConfiguration['backend'];
            $fieldStructureElement = [
                'type'                 => $formType,
                'label'                => $this->getFormTypeLabel($beConfig),
                'icon_class'           => $this->getFormTypeIcon($beConfig),
                'constraints'          => $this->getFormTypeAllowedConstraints($beConfig),
                'output_workflow'      => $this->getFormTypeOutputWorkflowConfiguration($beConfig),
                'configuration_layout' => $this->getFormTypeBackendConfiguration($beConfig, $formType)
            ];

            if ($flat === true) {
                $fieldStructure[] = $fieldStructureElement;
                continue;
            }

            $groupIndex = array_search($this->getFormTypeGroup($beConfig), array_column($fieldStructure, 'id'));

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
     * @param array $conditionalData
     *
     * @return array
     *
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
    private function getTranslatedContainerTypes()
    {
        $containerTypes = $this->configuration->getAvailableContainer();

        $containerData = [];
        foreach ($containerTypes as $containerId => &$container) {
            $container['label'] = $this->translate($container['label']);
            if (isset($container['configuration']) && is_array($container['configuration'])) {
                foreach ($container['configuration'] as $index => $configNode) {
                    if (isset($container['configuration'][$index]['label'])) {
                        $container['configuration'][$index]['label'] = $this->translate($configNode['label']);
                    }
                }
            }
            $containerData[] = $container;
        }

        return $containerData;
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
     * @param array  $formTypeBackendConfig
     * @param string $formType
     *
     * @return array
     */
    private function getFormTypeBackendConfiguration($formTypeBackendConfig, $formType)
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
     * @param string $formType
     * @param array  $formTypeBackendConfig
     *
     * @return array
     */
    private function getMergedFormTypeConfig($formType, $formTypeBackendConfig = null)
    {
        if (is_null($formTypeBackendConfig)) {
            throw new InvalidConfigurationException(sprintf('No valid form field configuration for "%s" found.', $formType));
        }

        $baseConfig = $this->configuration->getBackendConfig('backend_base_field_type_config');
        $tabs = array_merge($baseConfig['tabs'], $formTypeBackendConfig['tabs']);
        $displayGroups = array_merge($baseConfig['display_groups'], $formTypeBackendConfig['display_groups']);
        $fields = array_merge($baseConfig['fields'], $formTypeBackendConfig['fields']);

        return ['tabs' => $tabs, 'displayGroups' => $displayGroups, 'fields' => $fields];
    }

    /**
     * @param array $formTypeBackendConfig
     *
     * @return mixed
     */
    private function getFormTypeGroup(array $formTypeBackendConfig)
    {
        return $formTypeBackendConfig['form_type_group'];
    }

    /**
     * @param array $formTypeBackendConfig
     *
     * @return mixed
     */
    private function getFormTypeIcon(array $formTypeBackendConfig)
    {
        return $formTypeBackendConfig['icon_class'];
    }

    /**
     * @param array $formTypeBackendConfig
     *
     * @return string
     */
    private function getFormTypeLabel(array $formTypeBackendConfig)
    {
        return $this->translate($formTypeBackendConfig['label']);
    }

    /**
     * @param array $formTypeBackendConfig
     *
     * @return mixed
     */
    private function getFormTypeAllowedConstraints($formTypeBackendConfig)
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
     * @param array $formTypeBackendConfig
     *
     * @return array
     */
    private function getFormTypeOutputWorkflowConfiguration($formTypeBackendConfig)
    {
        return $formTypeBackendConfig['output_workflow'];
    }

    /**
     * Get translated Form Type Templates.
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
     * @param string $value
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
     * @throws \Exception
     */
    private function transformFieldOptions(&$fieldData, $reverse = false)
    {
        $formTypes = $this->configuration->getConfig('types');

        if (!isset($fieldData['options']) || !is_array($fieldData['options'])) {
            return;
        }

        $formTypeConfig = $formTypes[$fieldData['type']];
        $backendConfig = $this->getMergedFormTypeConfig($fieldData['type'], $formTypeConfig['backend']);

        if (!isset($backendConfig['fields'])) {
            return;
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
     * @param array $fieldData
     * @param bool  $reverse
     *
     * @throws \Exception
     */
    private function transformContainerOptions(&$fieldData, $reverse = false)
    {
        if (!isset($fieldData['configuration']) || !is_array($fieldData['configuration'])) {
            return;
        }

        $formContainerTypes = $this->configuration->getAvailableContainer();
        $containerData = $formContainerTypes[$fieldData['sub_type']];
        if (!isset($containerData['configuration']) || !is_array($containerData['configuration'])) {
            return;
        }

        $containerConfigurations = $containerData['configuration'];
        $currentConfiguration = $fieldData['configuration'];

        foreach ($containerConfigurations as $containerConfiguration) {
            $configName = $containerConfiguration['name'];
            if (!isset($currentConfiguration[$configName])) {
                continue;
            }

            $blockValue = $currentConfiguration[$configName];
            $blockConfig = $containerConfiguration['config'];

            if (!empty($containerConfiguration['options_transformer'])) {
                /** @var OptionsTransformerInterface $transformer */
                $transformer = $this->optionsTransformerRegistry->get($containerConfiguration['options_transformer']);

                if ($reverse === false) {
                    $fieldData['configuration'][$configName] = $transformer->transform($blockValue, $blockConfig);
                } else {
                    $fieldData['configuration'][$configName] = $transformer->reverseTransform($blockValue, $blockConfig);
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
