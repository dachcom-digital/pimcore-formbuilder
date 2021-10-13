<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ConditionTrait;

class ElementValueCondition implements ConditionInterface
{
    use ConditionTrait;

    protected array $fields = [];
    protected string $comparator = '';
    protected string|array $value = '';

    public function isValid(array $formData, int $ruleId, array $configuration = []): bool
    {
        foreach ($this->getFields() as $conditionFieldName) {
            $fieldValue = $formData[$conditionFieldName] ?? null;

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

    public function setComparator($comparator): void
    {
        $this->comparator = $comparator;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields($fields): void
    {
        $this->fields = $fields;
    }

    public function getValue(): string|array
    {
        return $this->value;
    }

    public function setValue(string|array $value): void
    {
        $this->value = $value;
    }
}
