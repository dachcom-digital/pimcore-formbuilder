<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class SimpleReturnStack implements ReturnStackInterface
{
    public function __construct(
        protected string $actionType,
        protected mixed $data
    ) {
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
