<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition\ConditionInterface;

trait ConditionTrait
{
    public function setValues(array $values): ConditionInterface
    {
        foreach ($values as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }

        return $this;
    }
}
