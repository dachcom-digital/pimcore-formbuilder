<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

class ChannelContext
{
    protected array $contextData = [];

    public function getAllContextData(): array
    {
        return $this->contextData;
    }

    public function hasContextData(string $key): bool
    {
        return array_key_exists($key, $this->contextData);
    }

    public function getContextData(string $key): mixed
    {
        return $this->contextData[$key] ?? [];
    }

    public function addContextData(string $key, mixed $data): void
    {
        $this->contextData[$key] = $data;
    }
}
