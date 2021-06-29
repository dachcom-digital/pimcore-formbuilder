<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\ModuleInterface;

class DispatcherRegistry
{
    protected array $services = [];

    public function register(string $identifier, ModuleInterface $service): void
    {
        $this->services[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get(string  $identifier): ModuleInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dispatcher service does not exist.');
        }

        return $this->services[$identifier];
    }
}
