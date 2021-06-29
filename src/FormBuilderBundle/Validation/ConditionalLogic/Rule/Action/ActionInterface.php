<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\RuleInterface;

interface ActionInterface extends RuleInterface
{
    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface;
}
