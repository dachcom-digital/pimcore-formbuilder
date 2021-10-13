<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Factory;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;

class DataFactory
{
    protected iterable $dataHandler;

    public function __construct(iterable $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    public function generate(string $serviceId): ?DataInterface
    {
        foreach ($this->dataHandler as $dataHandler) {
            if ($dataHandler instanceof $serviceId) {
                return $dataHandler;
            }
        }

        return null;
    }
}
