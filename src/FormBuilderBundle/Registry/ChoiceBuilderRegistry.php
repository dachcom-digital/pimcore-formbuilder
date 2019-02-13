<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\ChoiceBuilderInterface;

class ChoiceBuilderRegistry
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param string                 $identifier
     * @param string                 $label
     * @param ChoiceBuilderInterface $service
     */
    public function register($identifier, $label, $service)
    {
        if (!in_array(ChoiceBuilderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ChoiceBuilderInterface::class, implode(', ', class_implements($service)))
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
     * @return ChoiceBuilderInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" choice builder service does not exist.');
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
