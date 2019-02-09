<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

interface ConditionInterface
{
    /**
     * @param array $formData
     * @param int   $ruleId
     *
     * @return mixed
     */
    public function isValid($formData, $ruleId);

    /**
     * @param array $values
     *
     * @return ConditionInterface
     */
    public function setValues(array $values);
}