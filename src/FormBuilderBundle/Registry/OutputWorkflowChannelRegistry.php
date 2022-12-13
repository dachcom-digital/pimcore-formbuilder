<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\FunnelAwareChannelInterface;

class OutputWorkflowChannelRegistry
{
    protected array $channels = [];

    public function register(string $identifier, mixed $service): void
    {
        if (isset($this->channels[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Output Channel with identifier "%s" already exists', $identifier));
        }

        if (!in_array(ChannelInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    ChannelInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $this->channels[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->channels[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): ChannelInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" output workflow channel does not exist.');
        }

        return $this->channels[$identifier];
    }

    public function getAll(): array
    {
        return $this->channels;
    }

    public function getAllIdentifier(): array
    {
        return array_keys($this->channels);
    }

    public function isFunnelAwareChannel(string $identifier): bool
    {
        if (!$this->has($identifier)) {
            return false;
        }

        return $this->get($identifier) instanceof FunnelAwareChannelInterface;
    }
}
