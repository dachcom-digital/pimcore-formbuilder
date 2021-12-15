<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\FieldTransformerInterface;

class FieldTransformerRegistry
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param string                    $identifier
     * @param FieldTransformerInterface $service
     */
    public function register($identifier, $service)
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
     * @return FieldTransformerInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('Field transform "' . $identifier . '" does not exist.');
        }

        return $this->services[$identifier];
    }

    /**
     * @return FieldTransformerInterface[]
     */
    public function getAll()
    {
        return $this->services;
    }
}
