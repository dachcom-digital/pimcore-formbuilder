<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

interface ReturnStackInterface
{
    public function getData(): array;

    public function getActionType(): string;
}
