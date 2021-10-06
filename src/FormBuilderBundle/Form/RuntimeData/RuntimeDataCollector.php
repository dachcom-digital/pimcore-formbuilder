<?php

namespace FormBuilderBundle\Form\RuntimeData;

class RuntimeDataCollector
{
    protected array $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @throws \Exception
     */
    public function add($id, $data): void
    {
        if (array_key_exists($id, $this->data)) {
            throw new \Exception(sprintf('Runtime Data Block with "%s" already added.', $id));
        }

        $this->data[$id] = $data;
    }

    /**
     * @throws \Exception
     */
    public function find(string $id): mixed
    {
        if (!array_key_exists($id, $this->data)) {
            throw new \Exception(sprintf('Runtime Data Block with "%s" not found.', $id));
        }

        return $this->data[$id];
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @throws \Exception
     */
    public function __toString(): string
    {
        if (count($this->data) === 0) {
            return '';
        }

        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
