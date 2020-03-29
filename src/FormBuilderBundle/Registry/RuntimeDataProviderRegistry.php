<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;

class RuntimeDataProviderRegistry
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param RuntimeDataProviderInterface $service
     */
    public function register($service)
    {
        if (!in_array(RuntimeDataProviderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), RuntimeDataProviderInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[] = $service;
    }

    /**
     * @return RuntimeDataProviderInterface[]
     */
    public function getAll()
    {
        return $this->services;
    }
}
