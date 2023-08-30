<?php

namespace FormBuilderBundle\Event\Form;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;

class PreSubmitEvent extends Event
{
    public function __construct(
        private readonly FormEvent $formEvent,
        private readonly array $formOptions
    ) {
    }

    public function getFormEvent(): FormEvent
    {
        return $this->formEvent;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }
}
