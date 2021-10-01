<?php

namespace FormBuilderBundle\Transformer\Output\Traits;

use Pimcore\Model\DataObject\ClassDefinition\Data\Gender;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

trait ChoiceTargetTransformerTrait
{
    public function parseChoiceValue(mixed $target, mixed $rawValue): mixed
    {
        if ($target instanceof Select || $target instanceof Gender) {
            return $this->parseArrayChoiceToSingle($rawValue);
        }

        if ($target instanceof Multiselect) {
            return $this->parseSingleChoiceToArray($rawValue);
        }

        if ($target instanceof Input) {
            return $this->parseArrayChoiceToString($rawValue);
        }

        return $rawValue;
    }

    private function parseSingleChoiceToArray(mixed $rawValue): array
    {
        return !is_array($rawValue) ? [$rawValue] : $rawValue;
    }

    private function parseArrayChoiceToSingle(mixed $rawValue): mixed
    {
        if (!is_array($rawValue)) {
            return $rawValue;
        }

        if (count($rawValue) > 0) {
            return $rawValue[0];
        }

        return null;
    }

    private function parseArrayChoiceToString(mixed $rawValue): mixed
    {
        return is_array($rawValue) ? implode(', ', $rawValue) : $rawValue;
    }
}
