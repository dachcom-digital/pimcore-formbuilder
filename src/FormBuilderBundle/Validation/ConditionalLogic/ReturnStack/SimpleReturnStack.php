<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class SimpleReturnStack implements ReturnStackInterface
{
    public string $actionType;
    public array $data = [];

    public function __construct(string $actionType, array $data = [])
    {
        $this->actionType = $actionType;
        $this->data = $data;
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
