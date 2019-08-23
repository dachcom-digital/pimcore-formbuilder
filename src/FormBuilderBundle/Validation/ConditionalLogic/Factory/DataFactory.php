<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Factory;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;

class DataFactory
{
    /**
     * @var array
     */
    protected $dataHandler;

    /**
     * @param array $dataHandler
     */
    public function __construct($dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param string $serviceId
     *
     * @return DataInterface
     */
    public function generate($serviceId)
    {
        foreach ($this->dataHandler as $dataHandler) {
            if ($dataHandler instanceof $serviceId) {
                return $dataHandler;
            }
        }
    }
}
