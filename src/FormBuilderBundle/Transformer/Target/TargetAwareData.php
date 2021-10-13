<?php

namespace FormBuilderBundle\Transformer\Target;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class TargetAwareData
{
    protected FieldDefinitionInterface $fieldDefinition;
    protected FormInterface $formField;
    protected mixed $target;
    protected mixed $rawValue;
    protected ?string $locale;

    public function __construct(mixed $target, FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale)
    {
        $this->target = $target;
        $this->fieldDefinition = $fieldDefinition;
        $this->formField = $formField;
        $this->rawValue = $rawValue;
        $this->locale = $locale;
    }

    public function getTarget(): mixed
    {
        return $this->target;
    }

    public function getFieldDefinition(): FieldDefinitionInterface
    {
        return $this->fieldDefinition;
    }

    public function getFormField(): FormInterface
    {
        return $this->formField;
    }

    public function getRawValue(): mixed
    {
        return $this->rawValue;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
