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
     * {@inheritdoc}
     */
    public function isValid($formData, $ruleId, $configuration = [])
    {
        foreach ($this->getFields() as $conditionFieldName) {
            $fieldValue = isset($formData[$conditionFieldName]) ? $formData[$conditionFieldName] : null;

            if ($this->getComparator() === 'contains') {
                $value = is_array($this->getValue()) ? $this->getValue() : (is_string($this->getValue()) ? explode(',', $this->getValue()) : [$this->getValue()]);

                return !empty(array_intersect($value, (array) $fieldValue));
            } elseif ($this->getComparator() === 'is_checked') {
                return array_key_exists($conditionFieldName, $formData) && !empty($fieldValue);
            } elseif ($this->getComparator() === 'is_not_checked') {
                return empty($fieldValue);
            } elseif ($this->getComparator() === 'is_greater') {
                return $this->getValue() > $fieldValue;
            } elseif ($this->getComparator() === 'is_less') {
                return $this->getValue() < $fieldValue;
            } elseif ($this->getComparator() === 'is_value') {
                //could be an array (multiple)
                return $this->getValue() == $fieldValue || in_array($this->getValue(), (array) $fieldValue);
            } elseif ($this->getComparator() === 'is_empty_value') {
                return empty($fieldValue);
            } elseif ($this->getComparator() === 'is_not_value') {
                return $this->getValue() != $fieldValue;
            }
        }

        return false;
    }

    /**
     * @return string
     *
     * @internal
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * @param string $comparator
     *
     * @internal
     */
    public function setComparator($comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @return array
     *
     * @internal
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     *
     * @internal
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string|array
     *
     * @internal
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|array $value
     *
     * @internal
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
