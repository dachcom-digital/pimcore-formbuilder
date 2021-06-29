<?php

namespace FormBuilderBundle\Transformer\Target;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class TargetAwareData
{
    protected $target;
    protected FieldDefinitionInterface $fieldDefinition;
    protected FormInterface $formField;
    protected $rawValue;
    protected ?string $locale;

    public function __construct($target, FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, ?string $locale)
    {
        $this->target = $target;
        $this->fieldDefinition = $fieldDefinition;
        $this->formField = $formField;
        $this->rawValue = $rawValue;
        $this->locale = $locale;
    }

    public function getTarget()
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

    public function getRawValue()
    {
        return $this->rawValue;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
