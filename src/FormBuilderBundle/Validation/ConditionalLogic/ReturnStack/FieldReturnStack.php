<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class FieldReturnStack implements ReturnStackInterface
{
    public string $actionType;
    public array $data = [];

    /**
     * @throws \Exception
     */
    public function __construct(string $actionType = null, array $data = [])
    {
        if ($this->isAssoc($this->data)) {
            throw new \Exception('FieldReturnStack: Wrong data structure: data keys must contain form field names!');
        }

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

    public function updateData(array $data): void
    {
        $this->data = $data;
    }

    private function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
