<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\DynamicObjectResolver\DynamicObjectResolverInterface;

class DynamicObjectResolverRegistry
{
    protected array $services = [];

    public function register(string  $identifier, string  $label, DynamicObjectResolverInterface $service)
    {
        $this->services[$identifier] = ['service' => $service, 'label' => $label];
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    public function get(string $identifier): DynamicObjectResolverInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dynamic object resolver service does not exist.');
        }

        return $this->services[$identifier]['service'];
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
