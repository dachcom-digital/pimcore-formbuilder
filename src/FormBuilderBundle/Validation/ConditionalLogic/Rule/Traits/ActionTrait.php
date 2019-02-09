<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;

trait ActionTrait
{
    /**
     * @param array $values
     *
     * @return $this
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

    /**
     * @param array $formData
     * @param int   $ruleId
     *
     * @return ReturnStackInterface
     */
    public function apply($formData, $ruleId)
    {
        $data = [];

        return new SimpleReturnStack(self::class, $data);
    }
}