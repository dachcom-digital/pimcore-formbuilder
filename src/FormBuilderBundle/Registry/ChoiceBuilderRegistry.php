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
     * @param $identifier
     * @param $label
     * @param $service
     */
    public function register($identifier, $label, $service)
    {
        if (!in_array(ChoiceBuilderInterface::class, class_implements($service), TRUE)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ChoiceBuilderInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = ['service' => $service, 'label' => $label];
    }

    /**
     * @param $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @param $identifier
     * @return ChoiceBuilderInterface
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" choice builder service does not exist.');
        }

        return $this->services[$identifier]['service'];
    }

    public function getAll()
    {
        return $this->services;
    }
}
