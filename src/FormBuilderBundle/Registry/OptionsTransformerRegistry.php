<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\Transformer\OptionsTransformerInterface;

class OptionsTransformerRegistry
{
    protected array $transformer;
    protected array $dynamicTransformer;

    public function register(string $identifier, OptionsTransformerInterface $service): void
    {
        $this->transformer[$identifier] = $service;
    }

    public function registerDynamic(string $identifier, OptionsTransformerInterface $service): void
    {
        $this->dynamicTransformer[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->transformer[$identifier]);
    }

    public function hasDynamic(string $identifier): bool
    {
        return isset($this->dynamicTransformer[$identifier]);
    }

    public function get(string $identifier): OptionsTransformerInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception(sprintf('options transformer "%s" does not exist', $identifier));
        }

        return $this->transformer[$identifier];
    }

    public function getDynamic(string $identifier): OptionsTransformerInterface
    {
        if (!$this->hasDynamic($identifier)) {
            throw new \Exception(sprintf('dynamic options transformer "%s" does not exist', $identifier));
        }

        return $this->dynamicTransformer[$identifier];
    }
}
