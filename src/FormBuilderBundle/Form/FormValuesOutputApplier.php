<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\Type\Container\ContainerType;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use FormBuilderBundle\Registry\OutputTransformerRegistry;
use FormBuilderBundle\Transformer\Output\OutputTransformerInterface;
use FormBuilderBundle\Transformer\Target\TargetAwareOutputTransformer;
use FormBuilderBundle\Transformer\Target\TargetAwareValue;
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
     * @param FormFieldDefinitionInterface $fieldDefinition
     * @param FormInterface                $formField
     * @param string                       $locale
     * @param mixed                        $fieldRawValue
     *
     * @return array|null
     */
    protected function parseField(FormDataInterface $formData, $fieldDefinition, FormInterface $formField, $locale, $fieldRawValue)
    {
        if ($fieldDefinition instanceof FormFieldContainerDefinitionInterface) {
            $subFieldValues = [];
            foreach ($this->getFormSubFields($formField) as $index => $subFieldCollection) {
                $subCollectionFieldValues = [];
                /** @var FormFieldDefinitionInterface $subEntityField */
                foreach ($fieldDefinition->getFields() as $subEntityField) {
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

            return $this->transformFormBuilderContainerField($fieldDefinition, $subFieldValues, $locale);
        } elseif ($fieldDefinition instanceof FormFieldDynamicDefinitionInterface) {
            return $this->transformDynamicField($fieldDefinition, $formField, $fieldRawValue, $locale);
        } elseif ($fieldDefinition instanceof FormFieldDefinitionInterface) {
            return $this->transformFormBuilderField($fieldDefinition, $formField, $fieldRawValue, $locale);
        }

        return null;
    }

    /**
     * @param FormFieldContainerDefinitionInterface $fieldDefinition
     * @param array                                 $subFormFields
     * @param string                                $locale
     *
     * @return array|null
     */
    protected function transformFormBuilderContainerField(FormFieldContainerDefinitionInterface $fieldDefinition, array $subFormFields, $locale)
    {
        $fieldConfig = $fieldDefinition->getConfiguration();
        $containerLabel = isset($fieldConfig['label']) && !empty($fieldConfig['label']) ? $fieldConfig['label'] : false;
        $blockLabel = isset($fieldConfig['block_label']) && !empty($fieldConfig['block_label']) ? $fieldConfig['block_label'] : false;

        $data = [
            'field_type'  => FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER,
            'label'       => $containerLabel,
            'block_label' => $blockLabel,
            'name'        => $fieldDefinition->getName(),
            'type'        => $fieldDefinition->getSubType(),
            'fields'      => $subFormFields
        ];

        return $data;
    }

    /**
     * @param FormFieldDynamicDefinitionInterface $fieldDefinition
     * @param FormInterface                       $formField
     * @param mixed                               $rawValue
     * @param string                              $locale
     *
     * @return null|array
     */
    protected function transformDynamicField(FormFieldDynamicDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        $optionals = $fieldDefinition->getOptional();
        $outputTransformerIdentifier = isset($optionals['output_transformer']) && !empty($optionals['output_transformer']) ? $optionals['output_transformer'] : '';

        return $this->parseFieldValueWithOutputTransformer($fieldDefinition, $formField, $outputTransformerIdentifier, $rawValue, $locale);
    }

    /**
     * @param FormFieldDefinitionInterface $fieldDefinition
     * @param FormInterface                $formField
     * @param mixed                        $rawValue
     * @param string                       $locale
     *
     * @return null|array
     */
    protected function transformFormBuilderField(FormFieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        $formType = $this->configuration->getFieldTypeConfig($fieldDefinition->getType());
        $outputTransformerIdentifier = isset($formType['output_transformer']) && !empty($formType['output_transformer']) ? $formType['output_transformer'] : '';

        return $this->parseFieldValueWithOutputTransformer($fieldDefinition, $formField, $outputTransformerIdentifier, $rawValue, $locale);
    }

    /**
     * @param string                   $outputTransformerIdentifier
     * @param FieldDefinitionInterface $fieldDefinition
     * @param FormInterface            $formField
     * @param mixed                    $rawValue
     * @param string                   $locale
     *
     * @return array|null
     */
    protected function parseFieldValueWithOutputTransformer(
        FieldDefinitionInterface $fieldDefinition,
        FormInterface $formField,
        $outputTransformerIdentifier,
        $rawValue,
        $locale
    ) {
        $defaults = [
            'field_type' => FormValuesOutputApplierInterface::FIELD_TYPE_SIMPLE,
            'name'       => $fieldDefinition->getName(),
            'type'       => $fieldDefinition->getType()
        ];

        $outputTransformer = $this->getOutputTransformByIdentifier($outputTransformerIdentifier);
        if (!$outputTransformer instanceof OutputTransformerInterface) {
            return null;
        }

        $value = $outputTransformer->getValue($fieldDefinition, $formField, $rawValue, $locale);

        if ($value instanceof TargetAwareValue) {
            $value = new TargetAwareOutputTransformer($value, [$fieldDefinition, $formField, $rawValue, $locale]);
        }

        if ($this->isEmptyValue($value)) {
            return null;
        }

        return array_merge([
            'label'       => $outputTransformer->getLabel($fieldDefinition, $formField, $rawValue, $locale),
            //email_label is deprecated
            'email_label' => $outputTransformer->getLabel($fieldDefinition, $formField, $rawValue, $locale),
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
     * @param string $identifier
     *
     * @return null|OutputTransformerInterface
     */
    protected function getOutputTransformByIdentifier(string $identifier)
    {
        try {
            if ($this->outputTransformerRegistry->hasForChannel($identifier, $this->channel)) {
                return $this->outputTransformerRegistry->getForChannel($identifier, $this->channel);
            } elseif ($this->outputTransformerRegistry->hasForChannel(OutputTransformerRegistry::FALLBACK_TRANSFORMER_IDENTIFIER, $this->channel)) {
                return $this->outputTransformerRegistry->getForChannel(OutputTransformerRegistry::FALLBACK_TRANSFORMER_IDENTIFIER, $this->channel);
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
