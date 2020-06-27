<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits;

trait ActionTrait
{
    /**
     * {@inheritdoc}
     */
    public function setValues(array $values)
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
