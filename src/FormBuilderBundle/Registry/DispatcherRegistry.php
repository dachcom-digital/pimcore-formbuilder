<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\ModuleInterface;

class DispatcherRegistry
{
    protected array $services = [];

    public function register(string $identifier, mixed $service): void
    {
        if (!in_array(ModuleInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ModuleInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): ModuleInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dispatcher service does not exist.');
        }

        return $this->services[$identifier];
    }
}
