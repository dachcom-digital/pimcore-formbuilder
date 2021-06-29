<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule;

interface RuleInterface
{
    public function setValues(array $values): static;
}
