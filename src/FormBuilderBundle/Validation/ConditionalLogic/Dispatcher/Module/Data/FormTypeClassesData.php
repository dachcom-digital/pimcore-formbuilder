<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

class FormTypeClassesData implements DataInterface
{
    private array $data = [];

    /**
     * @param array $data
     */
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
}
