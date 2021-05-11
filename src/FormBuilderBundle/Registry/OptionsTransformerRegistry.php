<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Transformer\OptionsTransformerInterface;

class OptionsTransformerRegistry
{
    /**
     * @var array
     */
    protected $transformer;

    /**
     * @var array
     */
    protected $dynamicTransformer;

    /**
     * @var string
     */
    private $optionsInterface;

    /**
     * @var string
     */
    private $dynamicOptionsInterface;

    /**
     * @param string $interface
     */
    public function __construct($optionsInterface, $dynamicOptionsInterface)
    {
        $this->optionsInterface = $optionsInterface;
        $this->dynamicOptionsInterface = $dynamicOptionsInterface;
    }

    /**
     * @param string                      $identifier
     * @param OptionsTransformerInterface $service
     */
    public function register($identifier, $service)
    {
        if (!in_array($this->optionsInterface, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->optionsInterface, implode(', ', class_implements($service)))
            );
        }

        $this->transformer[$identifier] = $service;
    }

    /**
     * @param string                      $identifier
     * @param OptionsTransformerInterface $service
     */
    public function registerDynamic($identifier, $service)
    {
        if (!in_array($this->dynamicOptionsInterface, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->dynamicOptionsInterface, implode(', ', class_implements($service)))
            );
        }

        $this->dynamicTransformer[$identifier] = $service;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->transformer[$identifier]);
    }
    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasDynamic($identifier)
    {
        return isset($this->dynamicTransformer[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception(sprintf('options transformer "%s" does not exist', $identifier));
        }

        return $this->transformer[$identifier];
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getDynamic($identifier)
    {
        if (!$this->hasDynamic($identifier)) {
            throw new \Exception(sprintf('dynamic options transformer "%s" does not exist', $identifier));
        }

        return $this->dynamicTransformer[$identifier];
    }
}
