<?php

namespace FormBuilderBundle\Transformer\Target;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class TargetAwareData
{
    /**
     * @var mixed
     */
    protected $target;

    /**
     * @var FieldDefinitionInterface
     */
    protected $fieldDefinition;

    /**
     * @var FormInterface
     */
    protected $formField;

    /**
     * @var mixed
     */
    protected $rawValue;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param mixed                    $target
     * @param FieldDefinitionInterface $fieldDefinition
     * @param FormInterface            $formField
     * @param mixed                    $rawValue
     * @param string                   $locale
     */
    public function __construct($target, FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        $this->target = $target;
        $this->fieldDefinition = $fieldDefinition;
        $this->formField = $formField;
        $this->rawValue = $rawValue;
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return FieldDefinitionInterface
     */
    public function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }

    /**
     * @return FormInterface
     */
    public function getFormField()
    {
        return $this->formField;
    }

    /**
     * @return mixed
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
