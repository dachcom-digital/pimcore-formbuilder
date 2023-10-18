<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

class SwitchOutputWorkflowData implements DataInterface
{
    public const IDENTIFIER = 'workflowName';

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

    public function hasOutputWorkflowName(): bool
    {
        return !empty($this->data[self::IDENTIFIER]);
    }

    public function getOutputWorkflowName(): ?string
    {
        if (!$this->hasOutputWorkflowName()) {
            return null;
        }

        return $this->data[self::IDENTIFIER];
    }
}
