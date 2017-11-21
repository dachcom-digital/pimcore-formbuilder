<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

interface ConditionInterface {

    /**
     * @param $formData
     * @param $ruleId
     * @return mixed
     */
    public function isValid($formData, $ruleId);

    /**
     * @param array $values
     * @return ConditionInterface
     */
    public function setValues(array $values);
}