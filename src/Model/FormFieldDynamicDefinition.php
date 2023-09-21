<?php

namespace FormBuilderBundle\Model;

class FormFieldDynamicDefinition implements FormFieldDynamicDefinitionInterface
{
    protected string $name;
    protected string $type;
    protected array $options;
    protected array $optional;

    public function __construct(string $name, string $type, array $options, array $optional = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->optional = $optional;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOptional(): array
    {
        return $this->optional;
    }

    public function getOrder(): int
    {
        $optional = $this->getOptional();
        if (isset($optional['order']) && is_numeric($optional['order'])) {
            return (int) $optional['order'];
        }

        return 0;
    }
}
