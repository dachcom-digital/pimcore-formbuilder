<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

interface ConditionInterface
{
    /**
     * @param array $formData
     * @param int   $ruleId
     * @param array $configuration
     *
     * @return bool
     */
    public function isValid($formData, $ruleId, $configuration = []);

    /**
     * @param array $values
     *
     * @return ConditionInterface
     */
    public function setValues(array $values);
}
