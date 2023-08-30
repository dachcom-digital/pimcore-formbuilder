<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

class SwitchOutputWorkflowData implements DataInterface
{
    public const IDENTIFIER = 'workflowId';

    private array $data = [];

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function hasOutputWorkflowId(): bool
    {
        return isset($this->data[self::IDENTIFIER]) && is_int($this->data[self::IDENTIFIER]);
    }

    public function getOutputWorkflowId(): ?int
    {
        if (!$this->hasOutputWorkflowId()) {
            return null;
        }

        return $this->data[self::IDENTIFIER];
    }
}
