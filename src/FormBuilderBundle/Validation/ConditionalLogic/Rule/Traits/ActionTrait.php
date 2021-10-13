<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Action\ActionInterface;

trait ActionTrait
{
    public function setValues(array $values): ActionInterface
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
