<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

interface OutputTransformerInterface
{
    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed;

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string;
}
