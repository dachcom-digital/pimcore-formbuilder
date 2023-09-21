<?php

namespace FormBuilderBundle\Registry;

class OptionsTransformerRegistry
{
    protected array $transformer;
    protected array $dynamicTransformer;
    protected string $optionsInterface;
    protected string $dynamicOptionsInterface;

    public function __construct(string $optionsInterface, string $dynamicOptionsInterface)
    {
        $this->optionsInterface = $optionsInterface;
        $this->dynamicOptionsInterface = $dynamicOptionsInterface;
    }

    public function register(string $identifier, mixed $service): void
    {
        if (!in_array($this->optionsInterface, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->optionsInterface, implode(', ', class_implements($service)))
            );
        }

        $this->transformer[$identifier] = $service;
    }

    public function registerDynamic(string $identifier, mixed $service): void
    {
        if (!in_array($this->dynamicOptionsInterface, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), $this->dynamicOptionsInterface, implode(', ', class_implements($service)))
            );
        }

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

    /**
     * @throws \Exception
     */
    public function get(string $identifier): mixed
    {
        if (!$this->has($identifier)) {
            throw new \Exception(sprintf('options transformer "%s" does not exist', $identifier));
        }

        return $this->transformer[$identifier];
    }

    /**
     * @throws \Exception
     */
    public function getDynamic(string $identifier): mixed
    {
        if (!$this->hasDynamic($identifier)) {
            throw new \Exception(sprintf('dynamic options transformer "%s" does not exist', $identifier));
        }

        return $this->dynamicTransformer[$identifier];
    }
}
