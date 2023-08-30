<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiProviderInterface;

class ApiProviderRegistry
{
    protected array $services = [];

    public function register(string $identifier, $service): void
    {
        if (!in_array(ApiProviderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ApiProviderInterface::class, implode(', ', class_implements($service)))
            );
        }

        if (isset($this->services[$identifier])) {
            throw new \InvalidArgumentException(sprintf('API Provider "%s" already has been registered.', $identifier));
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
    public function get(string $identifier): ApiProviderInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('Api provider "' . $identifier . '" does not exist.');
        }

        return $this->services[$identifier];
    }

    /**
     * @return array<int, ApiProviderInterface>
     */
    public function getAll(): array
    {
        return $this->services;
    }
}
