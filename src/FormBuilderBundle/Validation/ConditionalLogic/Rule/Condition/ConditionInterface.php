<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\RuleInterface;

interface ConditionInterface extends RuleInterface
{
    public function isValid(array $formData, bool $ruleId, array $configuration = []): bool;
}
