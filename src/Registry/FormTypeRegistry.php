<?php

namespace FormBuilderBundle\Registry;

class FormTypeRegistry
{
    /**
     * @var array
     */
    protected $types;

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
     * {@inheritdoc}
     */
    public function register($service, $alias)
    {
        if (!in_array($this->interface, class_implements($service), TRUE)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->interface, implode(', ', class_implements($service)))
            );
        }

        $this->types[$alias] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function has($alias)
    {
        return isset($this->types[$alias]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias)
    {
        if (!$this->has($alias)) {
            throw new \Exception('"' . $alias . '" Form Type Identifier does not exist');
        }

        return $this->types[$alias];
    }

    public function getAll()
    {
        return $this->types;
    }
}
