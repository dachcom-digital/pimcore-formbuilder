<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\FieldTransformerInterface;

class FieldTransformerRegistry
{
    protected array $services = [];

    public function register(string $identifier, $service): void
    {
        if (!in_array(FieldTransformerInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), FieldTransformerInterface::class, implode(', ', class_implements($service)))
            );
        }

        if (isset($this->services[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Field transform "%s" already has been registered.', $identifier));
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
    public function get(string $identifier): ?FieldTransformerInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('Field transform "' . $identifier . '" does not exist.');
        }

        return $this->services[$identifier];
    }

    /**
     * @return array<int, FieldTransformerInterface>
     */
    public function getAll(): array
    {
        return $this->services;
    }
}
