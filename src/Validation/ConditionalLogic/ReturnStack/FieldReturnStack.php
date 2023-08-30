<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class FieldReturnStack implements ReturnStackInterface
{
    /**
     * @throws \Exception
     */
    public function __construct(
        protected string $actionType,
        protected mixed $data
    ) {
        if (!$this->isAssoc($this->data)) {
            throw new \Exception('FieldReturnStack: Wrong data structure: data keys must contain form field names!');
        }
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function updateData(mixed $data): void
    {
        $this->data = $data;
    }

    private function isAssoc(mixed $arr): bool
    {
        if (!is_array($arr)) {
            return false;
        }

        if ($arr === []) {
            return true;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
