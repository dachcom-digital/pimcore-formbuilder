<?php

namespace FormBuilderBundle\Form\RuntimeData;

class FunnelFormRuntimeData
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function addFunnelFormData(string $namespace, array $data): void
    {
        if (count($data) === 0) {
            return;
        }

        $this->data[$namespace] = $data;
    }

    public function hasFunnelFormData(string $namespace): bool
    {
        return array_key_exists($namespace, $this->data) && count($this->data[$namespace]) > 0;
    }

    public function getFunnelFormData(string $namespace): ?array
    {
        return $this->data[$namespace] ?? null;
    }
}
