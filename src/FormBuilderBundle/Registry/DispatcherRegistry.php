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
     * @param string          $identifier
     * @param ModuleInterface $service
     */
    public function register($identifier, $service)
    {
        if (!in_array(ModuleInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ModuleInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @param string $identifier
     *
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
