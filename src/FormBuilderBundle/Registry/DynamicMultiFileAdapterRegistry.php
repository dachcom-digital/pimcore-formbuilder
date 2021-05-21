<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\DynamicMultiFile\Adapter\DynamicMultiFileAdapterInterface;

class DynamicMultiFileAdapterRegistry
{
    /**
     * @var array
     */
    protected $adapter = [];

    /**
     * @param string                           $identifier
     * @param DynamicMultiFileAdapterInterface $service
     */
    public function register($identifier, $service)
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

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->adapter[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return DynamicMultiFileAdapterInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dynamic multi file adapter does not exist.');
        }

        return $this->adapter[$identifier];
    }

    /**
     * @return array|DynamicMultiFileAdapterInterface[]
     */
    public function getAll()
    {
        return $this->adapter;
    }

    /**
     * @return array
     */
    public function getAllIdentifier()
    {
        return array_keys($this->adapter);
    }
}
