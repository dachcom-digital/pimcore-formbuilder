<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ConditionTrait;

class ElementValueCondition implements ConditionInterface
{
    use ConditionTrait;

    /**
     * @var string
     */
    protected $comparator;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string|array
     */
    protected $value;

    /**
     * @param               $formData
     * @param               $ruleId
     * @param               $configuration
     * @return bool
     */
    public function isValid($formData, $ruleId, $configuration = [])
    {
        foreach ($this->getFields() as $conditionFieldName) {
            $fieldValue = isset($formData[$conditionFieldName]) ? $formData[$conditionFieldName] : NULL;

            if ($this->getComparator() === 'is_selected') {
                return in_array($this->getValue(), (array)$fieldValue);
            } elseif ($this->getComparator() === 'is_greater') {
                return $this->getValue() > $fieldValue;
            } elseif ($this->getComparator() === 'is_less') {
                return $this->getValue() < $fieldValue;
            } elseif ($this->getComparator() === 'is_value') {
                return $this->getValue() == $fieldValue;
            }
        }

        return FALSE;
    }

    /**
     * @return string
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * @param string
     */
    public function setComparator($comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }


    /**
     * @return string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|array
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}