<?php

namespace FormBuilderBundle\Transformer\Output\Traits;

use Pimcore\Model\DataObject\ClassDefinition\Data\Gender;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

trait ChoiceTargetTransformerTrait
{
    /**
     * @param mixed $target
     * @param mixed $rawValue
     *
     * @return mixed|null
     */
    public function parseChoiceValue($target, $rawValue)
    {
        if ($target instanceof Select || $target instanceof Gender) {
            return $this->parseArrayChoiceToSingle($rawValue);
        } elseif ($target instanceof Multiselect) {
            return $this->parseSingleChoiceToArray($rawValue);
        } elseif ($target instanceof Input) {
            return $this->parseArrayChoiceToString($rawValue);
        }

        return $rawValue;
    }

    /**
     * @param mixed $rawValue
     *
     * @return array
     */
    private function parseSingleChoiceToArray($rawValue)
    {
        return !is_array($rawValue) ? [$rawValue] : $rawValue;
    }

    /**
     * @param mixed $rawValue
     *
     * @return string|null
     */
    private function parseArrayChoiceToSingle($rawValue)
    {
        if (!is_array($rawValue)) {
            return $rawValue;
        }

        if (count($rawValue) > 0) {
            return $rawValue[0];
        }

        return null;
    }

    /**
     * @param mixed $rawValue
     *
     * @return string|null
     */
    private function parseArrayChoiceToString($rawValue)
    {
        return is_array($rawValue) ? join(', ', $rawValue) : $rawValue;
    }
}
