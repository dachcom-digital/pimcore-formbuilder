<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\Type\Container\ContainerType;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use FormBuilderBundle\Registry\OutputTransformerRegistry;
use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
class FormValuesOutputApplier implements FormValuesOutputApplierInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var OutputTransformerRegistry
     */
    protected $outputTransformerRegistry;

    /**
     * @var null|string
     */
    protected $channel;

    /**
     * @param Configuration             $configuration
     * @param OutputTransformerRegistry $outputTransformerRegistry
     */
    public function __construct(Configuration $configuration, OutputTransformerRegistry $outputTransformerRegistry)
    {
        $this->configuration = $configuration;
        $this->outputTransformerRegistry = $outputTransformerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function applyForChannel(FormInterface $form, array $ignoreFields, string $channel, $locale)
    {
        $this->channel = $channel;

        $fieldValues = [];

        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        $orderedFields = $formData->getFormDefinition()->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        /** @var FormFieldDefinitionInterface $field */
        foreach ($orderedFields as $field) {
            if (in_array($field->getName(), $ignoreFields)) {
                continue;
            }

            $formField = $form->get($field->getName());
            $formFieldValue = $formData->getFieldValue($field->getName());

            $parsedField = $this->parseField($formData, $field, $formField, $locale, $formFieldValue);

            if ($parsedField !== null) {
                $fieldValues[$field->getName()] = $parsedField;
            }
        }

        return $fieldValues;
    }

    /**
     * @param FormDataInterface            $formData
     * @param FormFieldDefinitionInterface $entityField
     * @param FormInterface                $formField
     * @param string                       $locale
     * @param mixed                        $fieldRawValue
     *
     * @return array|null
     */
    protected function parseField(FormDataInterface $formData, $entityField, FormInterface $formField, $locale, $fieldRawValue)
    {
        if ($entityField instanceof FormFieldContainerDefinitionInterface) {
            $subFieldValues = [];
            foreach ($this->getFormSubFields($formField) as $index => $subFieldCollection) {
                $subCollectionFieldValues = [];
                foreach ($entityField->getFields() as $subEntityField) {
                    $subFormField = $subFieldCollection->get($subEntityField->getName());
                    $subFieldRawValue = is_array($fieldRawValue) && isset($fieldRawValue[$index][$subEntityField->getName()]) ? $fieldRawValue[$index][$subEntityField->getName()] : null;
                    $parsedSubField = $this->parseField($formData, $subEntityField, $subFormField, $locale, $subFieldRawValue);
                    if ($parsedSubField !== null) {
                        $subCollectionFieldValues[] = $parsedSubField;
                    }
                }
                if (count($subCollectionFieldValues) > 0) {
                    $subFieldValues[] = $subCollectionFieldValues;
                }
            }

            if (count($subFieldValues) === 0) {
                return null;
            }

            return $this->transformFormBuilderContainerField($formData, $entityField, $subFieldValues, $locale);
        } elseif ($entityField instanceof FormFieldDynamicDefinitionInterface) {
            return $this->transformDynamicField($formData, $entityField, $formField, $fieldRawValue, $locale);
        } elseif ($entityField instanceof FormFieldDefinitionInterface) {
            return $this->transformFormBuilderField($formData, $entityField, $formField, $fieldRawValue, $locale);
        }

        return null;
    }

    /**
     * @param FormDataInterface                     $formData
     * @param FormFieldContainerDefinitionInterface $entityField
     * @param array                                 $subFormFields
     * @param string                                $locale
     *
     * @return array|null
     */
    protected function transformFormBuilderContainerField(FormDataInterface $formData, FormFieldContainerDefinitionInterface $entityField, array $subFormFields, $locale)
    {
        $fieldConfig = $entityField->getConfiguration();
        $containerLabel = isset($fieldConfig['label']) && !empty($fieldConfig['label']) ? $fieldConfig['label'] : false;
        $blockLabel = isset($fieldConfig['block_label']) && !empty($fieldConfig['block_label']) ? $fieldConfig['block_label'] : false;

        $data = [
            'field_type'  => FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER,
            'label'       => $containerLabel,
            'block_label' => $blockLabel,
            'name'        => $entityField->getName(),
            'type'        => $entityField->getSubType(),
            'fields'      => $subFormFields
        ];

        return $data;
    }

    /**
     * @param FormDataInterface                   $formData
     * @param FormFieldDynamicDefinitionInterface $entityField
     * @param FormInterface                       $formField
     * @param mixed                               $rawValue
     * @param string                              $locale
     *
     * @return null|array
     */
    protected function transformDynamicField(FormDataInterface $formData, FormFieldDynamicDefinitionInterface $entityField, FormInterface $formField, $rawValue, $locale)
    {
        $optionals = $entityField->getOptional();

        $defaults = [
            'field_type' => FormValuesOutputApplierInterface::FIELD_TYPE_SIMPLE,
            'name'       => $entityField->getName(),
            'type'       => $entityField->getType()
        ];

        if (isset($optionals['output_transformer']) && !empty($optionals['output_transformer'])) {
            $outputTransformer = $this->getOutputTransformByIdentifier($optionals['output_transformer']);
        } else {
            $outputTransformer = $this->getDefaultOutputTransformer();
        }

        if ($outputTransformer === null) {
            return null;
        }

        $value = $outputTransformer->getValue($entityField, $formField, $rawValue, $locale);

        if ($this->isEmptyValue($value)) {
            return null;
        }

        return array_merge([
            'label'       => $outputTransformer->getLabel($entityField, $formField, $rawValue, $locale),
            //email_label is deprecated
            'email_label' => $outputTransformer->getLabel($entityField, $formField, $rawValue, $locale),
            'value'       => $value,
        ], $defaults);
    }

    /**
     * @param FormDataInterface            $formData
     * @param FormFieldDefinitionInterface $entityField
     * @param FormInterface                $formField
     * @param mixed                        $rawValue
     * @param string                       $locale
     *
     * @return null|array
     */
    protected function transformFormBuilderField(FormDataInterface $formData, FormFieldDefinitionInterface $entityField, FormInterface $formField, $rawValue, $locale)
    {
        $defaults = [
            'field_type' => FormValuesOutputApplierInterface::FIELD_TYPE_SIMPLE,
            'name'       => $entityField->getName(),
            'type'       => $entityField->getType()
        ];

        $outputTransformer = $this->getOutputTransformForType($entityField->getType());
        if ($outputTransformer === null) {
            return null;
        }

        $value = $outputTransformer->getValue($entityField, $formField, $rawValue, $locale);

        if ($this->isEmptyValue($value)) {
            return null;
        }

        return array_merge([
            'label'       => $outputTransformer->getLabel($entityField, $formField, $rawValue, $locale),
            //email_label is deprecated
            'email_label' => $outputTransformer->getLabel($entityField, $formField, $rawValue, $locale),
            'value'       => $value,
        ], $defaults);
    }

    /**
     * @param FormInterface $formField
     *
     * @return array|FormInterface[]
     */
    protected function getFormSubFields(FormInterface $formField)
    {
        if ($formField->getConfig()->getType()->getParent() === null ||
            !$formField->getConfig()->getType()->getParent()->getInnerType() instanceof ContainerType
        ) {
            return [];
        }

        if ($formField->count() === 0) {
            return [];
        }

        return $formField->all();
    }

    /**
     * @param string $type
     *
     * @return null|OutputTransformerInterface
     */
    protected function getOutputTransformForType(string $type)
    {
        $formType = $this->configuration->getFieldTypeConfig($type);
        $outputTransformer = $formType['output_transformer'];

        return $this->getOutputTransformByIdentifier($outputTransformer);
    }

    /**
     * @param string $identifier
     *
     * @return null|OutputTransformerInterface
     */
    protected function getOutputTransformByIdentifier(string $identifier)
    {
        try {
            if ($this->outputTransformerRegistry->hasForChannel($identifier, $this->channel)) {
                return $this->outputTransformerRegistry->getForChannel($identifier, $this->channel);
            }
        } catch (\Exception $e) {
            // fail silently.
        }

        return $this->getDefaultOutputTransformer();
    }

    /**
     * @return OutputTransformerInterface|null
     */
    protected function getDefaultOutputTransformer()
    {
        try {
            return $this->outputTransformerRegistry->getFallbackTransformer();
        } catch (\Exception $e) {
            // fail silently
        }

        return null;
    }

    /**
     * @param mixed $formFieldValue
     *
     * @return bool
     */
    protected function isEmptyValue($formFieldValue)
    {
        return empty($formFieldValue) && $formFieldValue !== 0 && $formFieldValue !== '0';
    }
}
