<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;

interface ActionInterface
{
    /**
     * @throws \Exception
     */
    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface;

    public function setValues(array $values): ActionInterface;
}
