<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\ModuleInterface;

class DispatcherRegistry
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param $identifier
     * @param $service
     */
    public function register($identifier, $service)
    {
        if (!in_array(ModuleInterface::class, class_implements($service), TRUE)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ModuleInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    /**
     * @param $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @param $identifier
     * @return ModuleInterface
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dispatcher service does not exist.');
        }

        return $this->services[$identifier];
    }
}
