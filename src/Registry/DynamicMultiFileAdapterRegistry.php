<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\DynamicMultiFile\Adapter\DynamicMultiFileAdapterInterface;

class DynamicMultiFileAdapterRegistry
{
    protected array $adapter = [];

    public function register(string $identifier, mixed $service): void
    {
        if (isset($this->adapter[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Dynamic multi file adapter with identifier "%s" already exists', $identifier));
        }

        if (!in_array(DynamicMultiFileAdapterInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    DynamicMultiFileAdapterInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->adapter[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->adapter[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): DynamicMultiFileAdapterInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dynamic multi file adapter does not exist.');
        }

        return $this->adapter[$identifier];
    }

    public function getAll(): array
    {
        return $this->adapter;
    }

    public function getAllIdentifier(): array
    {
        return array_keys($this->adapter);
    }
}
