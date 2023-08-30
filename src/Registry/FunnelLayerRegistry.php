<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerInterface;

class FunnelLayerRegistry
{
    protected array $funnelLayers = [];

    public function register(string $identifier, mixed $service): void
    {
        if (isset($this->funnelLayers[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Funnel Layer with identifier "%s" already exists', $identifier));
        }

        if (!in_array(FunnelLayerInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    FunnelLayerInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->funnelLayers[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->funnelLayers[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): FunnelLayerInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" funnel layer does not exist.');
        }

        return $this->funnelLayers[$identifier];
    }

    public function getAll(): array
    {
        return $this->funnelLayers;
    }

    public function getAllIdentifier(): array
    {
        return array_keys($this->funnelLayers);
    }
}
