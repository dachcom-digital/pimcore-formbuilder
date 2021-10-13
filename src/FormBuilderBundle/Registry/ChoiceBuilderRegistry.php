<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\ChoiceBuilderInterface;

class ChoiceBuilderRegistry
{
    protected array $services = [];

    public function register(string $identifier, string $label, mixed $service): void
    {
        if (!in_array(ChoiceBuilderInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), ChoiceBuilderInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->services[$identifier] = ['service' => $service, 'label' => $label];
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): ChoiceBuilderInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" choice builder service does not exist.');
        }

        return $this->services[$identifier]['service'];
    }

    public function getAll(): array
    {
        return $this->services;
    }
}
