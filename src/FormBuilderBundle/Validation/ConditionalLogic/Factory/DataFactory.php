<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Factory;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;

class DataFactory
{
    /**
     * @var iterable
     */
    protected $dataHandler;

    /**
     * DataFactory constructor.
     *
     * @param $dataHandler
     */
    public function __construct($dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param $serviceId
     * @return DataInterface|bool
     */
    public function generate($serviceId)
    {
        foreach ($this->dataHandler as $dataHandler) {
            if ($dataHandler instanceof $serviceId) {
                return $dataHandler;
            }
        }
        return false;
    }
}
