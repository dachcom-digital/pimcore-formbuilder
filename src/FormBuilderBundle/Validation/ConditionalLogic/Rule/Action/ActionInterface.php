<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;

interface ActionInterface
{
    /**
     * @param $validationState
     * @param $formData
     * @param $ruleId
     *
     * @return ReturnStackInterface
     */
    public function apply($validationState, $formData, $ruleId);

    /**
     * @param array $values
     * @return ActionInterface
     */
    public function setValues(array $values);
}