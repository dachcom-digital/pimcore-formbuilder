<?php

namespace FormBuilderBundle\Event\Form;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;

class PostSetDataEvent extends Event
{
    private FormEvent $formEvent;
    private array $formOptions;

    public function __construct(FormEvent $formEvent, $formOptions)
    {
        $this->formEvent = $formEvent;
        $this->formOptions = $formOptions;
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
