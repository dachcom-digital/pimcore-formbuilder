<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\FormBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

class FormListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $formOptions = [];

    /**
     * @var ContainerAwareEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * FormListener constructor.
     *
     * @param array $formOptions
     */
    public function __construct($formOptions = [])
    {
        $this->formOptions = $formOptions;
        $this->eventDispatcher = \Pimcore::getEventDispatcher();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => ['onPreSetData'],
            FormEvents::PRE_SUBMIT   => ['onPreSubmit']
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $preSetDataEvent = new PreSetDataEvent($event, $this->formOptions);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_PRE_SET_DATA, $preSetDataEvent);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $preSubmitEvent = new PreSubmitEvent($event, $this->formOptions);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_PRE_SUBMIT, $preSubmitEvent);
    }
}