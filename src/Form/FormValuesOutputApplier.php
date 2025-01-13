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
    protected ?string $channel;

    public function __construct(
        protected Configuration $configuration,
        protected OutputTransformerRegistry $outputTransformerRegistry
    ) {
    }

    public function applyForChannel(FormInterface $form, array $ignoreFields, string $channel, ?string $locale): array
    {
        $this->channel = $channel;

        $fieldValues = [];

        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        $orderedFields = $formData->getFormDefinition()->getFields();
        usort($orderedFields, static function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        foreach ($orderedFields as $field) {
            if (in_array($field->getName(), $ignoreFields, true)) {
                continue;
            }

            $formField = $form->get($field->getName());
            $formFieldValue = $formData->getFieldValue($field->getName());

            $parsedField = $this->parseField($formData, $field, $formField, $locale, $formFieldValue, $ignoreFields);

            if ($parsedField !== null) {
                $fieldValues[$field->getName()] = $parsedField;
            }
        }

        return $fieldValues;
    }

    protected function parseField(
        FormDataInterface $formData,
        FieldDefinitionInterface $fieldDefinition,
        FormInterface $formField,
        ?string $locale,
        mixed $fieldRawValue,
        array $ignoreFields
    ): ?array {
        if ($fieldDefinition instanceof FormFieldContainerDefinitionInterface) {
            $subFieldValues = [];
            foreach ($this->getFormSubFields($formField) as $index => $subFieldCollection) {
                $subCollectionFieldValues = [];
                /** @var FormFieldDefinitionInterface $subEntityField */
                foreach ($fieldDefinition->getFields() as $subEntityField) {
                    if (in_array($subEntityField->getName(), $ignoreFields, true)) {
                        continue;
                    }

                    $subFormField = $subFieldCollection->get($subEntityField->getName());
                    $subFieldRawValue = is_array($fieldRawValue) && isset($fieldRawValue[$index][$subEntityField->getName()]) ? $fieldRawValue[$index][$subEntityField->getName()] : null;
                    $parsedSubField = $this->parseField($formData, $subEntityField, $subFormField, $locale, $subFieldRawValue, $ignoreFields);
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
        }

        if ($fieldDefinition instanceof FormFieldDynamicDefinitionInterface) {
            return $this->transformDynamicField($fieldDefinition, $formField, $fieldRawValue, $locale);
        }

        if ($fieldDefinition instanceof FormFieldDefinitionInterface) {
            return $this->transformFormBuilderField($fieldDefinition, $formField, $fieldRawValue, $locale);
        }

        return null;
    }

    protected function transformFormBuilderContainerField(FormFieldContainerDefinitionInterface $fieldDefinition, array $subFormFields, ?string $locale): ?array
    {
        $fieldConfig = $fieldDefinition->getConfiguration();
        $containerLabel = isset($fieldConfig['label']) && !empty($fieldConfig['label']) ? $fieldConfig['label'] : false;
        $blockLabel = isset($fieldConfig['block_label']) && !empty($fieldConfig['block_label']) ? $fieldConfig['block_label'] : false;

        return [
            'field_type'  => FormValuesOutputApplierInterface::FIELD_TYPE_CONTAINER,
            'label'       => $containerLabel,
            'block_label' => $blockLabel,
            'name'        => $fieldDefinition->getName(),
            'type'        => $fieldDefinition->getSubType(),
            'fields'      => $subFormFields
        ];
    }

    protected function transformDynamicField(FormFieldDynamicDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?array
    {
        $optionals = $fieldDefinition->getOptional();
        $outputTransformerIdentifier = isset($optionals['output_transformer']) && !empty($optionals['output_transformer']) ? $optionals['output_transformer'] : '';

        return $this->parseFieldValueWithOutputTransformer($fieldDefinition, $formField, $outputTransformerIdentifier, $rawValue, $locale);
    }

    protected function transformFormBuilderField(FormFieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?array
    {
        $formType = $this->configuration->getFieldTypeConfig($fieldDefinition->getType());
        $outputTransformerIdentifier = isset($formType['output_transformer']) && !empty($formType['output_transformer']) ? $formType['output_transformer'] : '';

        return $this->parseFieldValueWithOutputTransformer($fieldDefinition, $formField, $outputTransformerIdentifier, $rawValue, $locale);
    }

    protected function parseFieldValueWithOutputTransformer(
        FieldDefinitionInterface $fieldDefinition,
        FormInterface $formField,
        string $outputTransformerIdentifier,
        mixed $rawValue,
        ?string $locale
    ): ?array {
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
     * @return array<int, FormInterface>
     */
    protected function getFormSubFields(FormInterface $formField): array
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

    protected function getOutputTransformByIdentifier(string $identifier): ?OutputTransformerInterface
    {
        try {
            if ($this->outputTransformerRegistry->hasForChannel($identifier, $this->channel)) {
                return $this->outputTransformerRegistry->getForChannel($identifier, $this->channel);
            }

            if ($this->outputTransformerRegistry->hasForChannel(OutputTransformerRegistry::FALLBACK_TRANSFORMER_IDENTIFIER, $this->channel)) {
                return $this->outputTransformerRegistry->getForChannel(OutputTransformerRegistry::FALLBACK_TRANSFORMER_IDENTIFIER, $this->channel);
            }
        } catch (\Exception $e) {
            // fail silently.
        }

        return $this->getDefaultOutputTransformer();
    }

    protected function getDefaultOutputTransformer(): ?OutputTransformerInterface
    {
        try {
            return $this->outputTransformerRegistry->getFallbackTransformer();
        } catch (\Exception $e) {
            // fail silently
        }

        return null;
    }

    protected function isEmptyValue(mixed $formFieldValue): bool
    {
        return empty($formFieldValue) && $formFieldValue !== 0 && $formFieldValue !== '0';
    }
}
