<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

interface ConditionInterface
{
    public function isValid(array $formData, int $ruleId, array $configuration = []): bool;

    public function setValues(array $values): ConditionInterface;
}
