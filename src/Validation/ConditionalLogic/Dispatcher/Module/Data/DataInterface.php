<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

interface DataInterface
{
    public function setData(array $data): void;

    public function hasData(): bool;

    public function getData(): mixed;
}
