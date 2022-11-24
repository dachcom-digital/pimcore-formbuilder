<?php

namespace FormBuilderBundle\Model;

class FunnelActionElement
{
    protected string $name;
    protected string $label;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
