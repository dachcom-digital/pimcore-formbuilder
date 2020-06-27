<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;

interface ActionInterface
{
    /**
     * @param bool  $validationState
     * @param array $formData
     * @param int   $ruleId
     *
     * @return ReturnStackInterface
     *
     * @throws \Exception
     */
    public function apply($validationState, $formData, $ruleId);

    /**
     * @param array $values
     *
     * @return ActionInterface
     */
    public function setValues(array $values);
}
