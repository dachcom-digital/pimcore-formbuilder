<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\DynamicObjectResolver\DynamicObjectResolverInterface;

class DynamicObjectResolverRegistry
{
    protected array $services = [];

    public function register(string $identifier, string $label, mixed $service): void
    {
        if (!in_array(DynamicObjectResolverInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), DynamicObjectResolverInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = ['service' => $service, 'label' => $label];
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @throws \Exception
     */
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
