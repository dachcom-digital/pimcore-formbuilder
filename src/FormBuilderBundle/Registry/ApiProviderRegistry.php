<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\Api\ApiProviderInterface;

class ApiProviderRegistry
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param string               $identifier
     * @param ApiProviderInterface $service
     */
    public function register($identifier, $service)
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

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return ApiProviderInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('Api provider "' . $identifier . '" does not exist.');
        }

        return $this->services[$identifier];
    }

    /**
     * @return ApiProviderInterface[]
     */
    public function getAll()
    {
        return $this->services;
    }
}
