<?php

namespace FormBuilderBundle\Model;

class FunnelActionDefinition
{
    protected string $name;
    protected string $label;
    protected array $parameters;

    public function __construct(string $name, string $label, array $parameters = [])
    {
        $this->name = $name;
        $this->label = $label;
        $this->parameters = $parameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
