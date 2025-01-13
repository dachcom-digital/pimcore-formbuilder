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
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use FormBuilderBundle\Registry\InputTransformerRegistry;
use FormBuilderBundle\Transformer\Input\InputTransformerInterface;

class FormValuesInputApplier implements FormValuesInputApplierInterface
{
    public function __construct(
        protected Configuration $configuration,
        protected InputTransformerRegistry $inputTransformerRegistry
    ) {
    }

    public function apply(array $form, FormDefinitionInterface $formDefinition): array
    {
        $fieldValues = [];
        foreach ($form as $fieldName => $formFieldValue) {
            $fieldDefinition = $formDefinition->getField($fieldName);
            if (!$fieldDefinition instanceof FieldDefinitionInterface) {
                continue;
            }

            $fieldValues[$fieldName] = $this->parseField($fieldDefinition, $formFieldValue);
        }

        return $fieldValues;
    }

    protected function parseField(FieldDefinitionInterface $fieldDefinition, mixed $fieldRawValue): mixed
    {
        if ($fieldDefinition instanceof FormFieldContainerDefinitionInterface) {
            $subFieldValues = [];
            foreach ($fieldRawValue as $index => $subFieldRawValueContainer) {
                $subCollectionFieldValues = [];
                /** @var FormFieldDefinitionInterface $subFieldDefinition */
                foreach ($fieldDefinition->getFields() as $subFieldDefinition) {
                    $subFieldName = $subFieldDefinition->getName();

                    if (!array_key_exists($subFieldName, $subFieldRawValueContainer)) {
                        continue;
                    }

                    $subCollectionFieldValues[$subFieldName] = $this->parseField($subFieldDefinition, $subFieldRawValueContainer[$subFieldName]);
                }

                $subFieldValues[$index] = $subCollectionFieldValues;
            }

            if (count($subFieldValues) === 0) {
                return null;
            }

            return $subFieldValues;
        }

        if ($fieldDefinition instanceof FormFieldDynamicDefinitionInterface) {
            return $this->transformField($fieldDefinition, $fieldRawValue);
        }

        if ($fieldDefinition instanceof FormFieldDefinitionInterface) {
            return $this->transformField($fieldDefinition, $fieldRawValue);
        }

        return null;
    }

    protected function transformField(FieldDefinitionInterface $fieldDefinition, mixed $rawValue): mixed
    {
        $transformer = $this->getInputTransformByIdentifier($fieldDefinition);

        if (!$transformer instanceof InputTransformerInterface) {
            return $rawValue;
        }

        return $transformer->getValueReverse($fieldDefinition, $rawValue);
    }

    protected function getInputTransformByIdentifier(FieldDefinitionInterface $fieldDefinition): ?InputTransformerInterface
    {
        $formType = $this->configuration->getFieldTypeConfig($fieldDefinition->getType());
        $inputTransformerIdentifier = $formType['input_transformer'] ?? null;

        if ($inputTransformerIdentifier === null) {
            return null;
        }

        return $this->inputTransformerRegistry->get($inputTransformerIdentifier);
    }
}
