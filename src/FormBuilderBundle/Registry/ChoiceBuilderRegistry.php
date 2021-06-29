<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Form\ChoiceBuilderInterface;

class ChoiceBuilderRegistry
{
    protected array $services = [];

    public function register(string $identifier, string $label, ChoiceBuilderInterface $service): void
    {
        $this->services[$identifier] = ['service' => $service, 'label' => $label];
    }

    public function has(string $identifier): bool
    {
        return isset($this->services[$identifier]);
    }

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
