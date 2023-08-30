<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;

class RuntimeDataProviderRegistry
{
    protected array $services = [];

    public function register(mixed $service): void
    {
        if (!in_array(RuntimeDataProviderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), RuntimeDataProviderInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[] = $service;
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
