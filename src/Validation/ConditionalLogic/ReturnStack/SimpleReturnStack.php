<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class SimpleReturnStack implements ReturnStackInterface
{
    public function __construct(
        protected string $actionType,
        protected array $data = []
    ) {
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
