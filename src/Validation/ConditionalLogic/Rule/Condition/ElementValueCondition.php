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
            $fieldValue = $this->getFieldValue($formData, $conditionFieldName);

            if ($this->getComparator() === 'contains') {
                $value = $this->getValue();
                if (!is_array($value)) {
                    $value = is_string($this->getValue()) && str_contains($this->getValue(), ',') ? explode(',', $this->getValue()) : [$this->getValue()];
                }

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

    protected function getFieldValue(array $formData, string $fieldname)
    {
        $fieldValue = $formData[$fieldname] ?? null;
        if ($fieldValue !== null) {
            return $fieldValue;
        }

        $containers = array_filter($formData, static function ($data) {
            return is_array($data) && !empty($data);
        });

        // iterate over container data until field is found
        foreach ($containers as $container) {
            $fieldValue = $this->getFieldValue($container, $fieldname);
            if ($fieldValue !== null) {
                break;
            }
        }

        return $fieldValue;
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
