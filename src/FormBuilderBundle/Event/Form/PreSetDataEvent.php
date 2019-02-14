<?php

namespace FormBuilderBundle\Event\Form;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;

class PreSetDataEvent extends Event
{
    /**
     * @var FormEvent
     */
    private $formEvent;

    /**
     * @var array
     */
    private $formOptions;

    /**
     * PreSetDataEvent constructor.
     *
     * @param FormEvent $formEvent
     * @param array     $formOptions
     */
    public function __construct(FormEvent $formEvent, $formOptions)
    {
        $this->formEvent = $formEvent;
        $this->formOptions = $formOptions;
    }

    /**
     * @return FormEvent
     */
    public function getFormEvent()
    {
        return $this->formEvent;
    }

    /**
     * @return array
     */
    public function getFormOptions()
    {
        return $this->formOptions;
    }
}
