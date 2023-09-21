<?php

namespace FormBuilderBundle\Form;

interface AdvancedChoiceBuilderInterface extends ChoiceBuilderInterface
{
    public function getChoiceValue(mixed $element = null): mixed;

    public function getChoiceLabel(mixed $choiceValue, string $key, mixed $value): mixed;

    public function getChoiceAttributes(mixed $element, string $key, mixed $value): mixed;

    public function getGroupBy(mixed $element, string $key, mixed $value): mixed;

    public function getPreferredChoices(mixed $element, string $key, mixed $value): mixed;
}
