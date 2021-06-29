<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;

class RuntimeDataProviderRegistry
{
    protected array $services = [];

    public function register(RuntimeDataProviderInterface $service)
    {
        $this->services[] = $service;
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
