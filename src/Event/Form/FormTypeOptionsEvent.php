<?php

namespace FormBuilderBundle\Event\Form;

use Symfony\Contracts\EventDispatcher\Event;

class FormTypeOptionsEvent extends Event
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $type,
        private array $options,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
