<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ConditionTrait;

class ElementValueCondition implements ConditionInterface
{
    use ConditionTrait;

    protected string $comparator;
    protected array $fields = [];

    /**
     * @var string|array
     */
    protected $value;

    public function isValid(array $formData, bool $ruleId, array $configuration = []): bool
    {
        foreach ($this->getFields() as $conditionFieldName) {
            $fieldValue = isset($formData[$conditionFieldName]) ? $formData[$conditionFieldName] : null;

            if ($this->getComparator() === 'contains') {
                $value = is_array($this->getValue()) ? $this->getValue() : (is_string($this->getValue()) ? explode(',', $this->getValue()) : [$this->getValue()]);

                return !empty(array_intersect($value, (array) $fieldValue));
            }

            if ($this->getComparator() === 'is_checked') {
                return array_key_exists($conditionFieldName, $formData) && !empty($fieldValue);
            }

            if ($this->getComparator() === 'is_not_checked') {
                return empty($fieldValue);
            }

            if ($this->getComparator() === 'is_greater') {
                return $this->getValue() > $fieldValue;
            }

            if ($this->getComparator() === 'is_less') {
                return $this->getValue() < $fieldValue;
            }

            if ($this->getComparator() === 'is_value') {
                //could be an array (multiple)
                return $this->getValue() == $fieldValue || in_array($this->getValue(), (array) $fieldValue);
            }

            if ($this->getComparator() === 'is_empty_value') {
                return empty($fieldValue);
            }

            if ($this->getComparator() === 'is_not_value') {
                return $this->getValue() != $fieldValue;
            }
        }

        return false;
    }

    public function getComparator(): string
    {
        return $this->comparator;
    }

    public function setComparator(string $comparator): void
    {
        $this->comparator = $comparator;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
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
