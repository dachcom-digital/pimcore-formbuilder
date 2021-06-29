<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Storage\FormFieldSimpleInterface;
use Symfony\Component\Form\FormInterface;

interface OutputTransformerInterface
{
    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, ?string $locale);

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, ?string $locale);
}
