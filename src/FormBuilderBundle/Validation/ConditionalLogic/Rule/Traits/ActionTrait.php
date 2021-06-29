<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits;

trait ActionTrait
{
    public function setValues(array $values): static
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
