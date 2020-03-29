<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\DynamicObjectResolver\DynamicObjectResolverInterface;

class DynamicObjectResolverRegistry
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param string                         $identifier
     * @param string                         $label
     * @param DynamicObjectResolverInterface $service
     */
    public function register($identifier, $label, $service)
    {
        if (!in_array(DynamicObjectResolverInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), DynamicObjectResolverInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = ['service' => $service, 'label' => $label];
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
     * @return DynamicObjectResolverInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" dynamic object resolver service does not exist.');
        }

        return $this->services[$identifier]['service'];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->services;
    }
}
