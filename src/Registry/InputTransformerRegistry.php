<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Transformer\Input\InputTransformerInterface;

class InputTransformerRegistry
{
    protected array $transformer = [];

    public function register(string $identifier, mixed $service): void
    {
        if (!in_array(InputTransformerInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    InputTransformerInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->transformer[$identifier] = $service;
    }

    public function get(string $identifier): ?InputTransformerInterface
    {
        return $this->transformer[$identifier];
    }

    public function getAll(): array
    {
        return $this->transformer;
    }
}
