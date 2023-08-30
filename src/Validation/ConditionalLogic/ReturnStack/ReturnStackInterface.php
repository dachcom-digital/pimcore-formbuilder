<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

interface ReturnStackInterface
{
    public function getData(): mixed;

    public function getActionType(): string;
}
