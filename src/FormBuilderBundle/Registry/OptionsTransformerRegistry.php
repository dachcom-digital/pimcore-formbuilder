<?php

namespace FormBuilderBundle\Registry;

class OptionsTransformerRegistry
{
    /**
     * @var array
     */
    protected $transformer;

    /**
     * @var string
     */
    private $interface;

    /**
     * @param string $interface
     */
    public function __construct($interface)
    {
        $this->interface = $interface;
    }

    /**
     * @param $identifier
     * @param $service
     */
    public function register($identifier, $service)
    {
        if (!in_array($this->interface, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->interface, implode(', ', class_implements($service)))
            );
        }

        $this->transformer[$identifier] = $service;
    }

    /**
     * @param $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->transformer[$identifier]);
    }

    /**
     * @param $identifier
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Options Transformer does not exist');
        }

        return $this->transformer[$identifier];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->transformer;
    }
}
