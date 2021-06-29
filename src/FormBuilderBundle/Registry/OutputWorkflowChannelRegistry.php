<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class OutputWorkflowChannelRegistry
{
    protected array $channels = [];

    public function register(string $identifier, ChannelInterface $service): void
    {
        if (isset($this->channels[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Output Channel with identifier "%s" already exists', $identifier));
        }

        $this->channels[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->channels[$identifier]);
    }

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
}
