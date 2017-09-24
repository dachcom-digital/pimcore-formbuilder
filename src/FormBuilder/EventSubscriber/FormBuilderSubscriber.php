<?php

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Storage\FormFieldDynamicInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Stream\PackageStream;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class FormBuilderSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $formOptions = [];

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var PackageStream
     */
    protected $packageStream;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var
     */
    private $availableConstraints;

    /**
     * @var
     */
    private $availableFormTypes;

    /**
     * FormListener constructor.
     *
     * @param Configuration   $configuration
     * @param PackageStream   $packageStream
     * @param EventDispatcher $eventDispatcher
     * @param Session         $session
     */
    public function __construct(
        Configuration $configuration,
        PackageStream $packageStream,
        EventDispatcher $eventDispatcher,
        Session $session
    ) {
        $this->configuration = $configuration;
        $this->packageStream = $packageStream;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;

        $this->availableConstraints = $this->configuration->getConfig('validation_constraints');
        $this->availableFormTypes = $this->configuration->getConfig('types');
    }

    /**
     * @param $formOptions
     */
    public function setFormOptions($formOptions)
    {
        $this->formOptions = $formOptions;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => ['onPreSetData'],
            FormEvents::POST_SET_DATA => ['onPostSetData'],
            FormEvents::PRE_SUBMIT    => ['onPreSubmit'],
            FormEvents::POST_SUBMIT   => ['onPostSubmit']
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $formEntity = $event->getData();

        $orderedFields = $formEntity->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        /** @var FormFieldInterface $field */
        foreach ($orderedFields as $field) {
            if ($field instanceof FormFieldDynamicInterface) {
                $this->addDynamicField($form, $field);
            } else {
                $this->addFormBuilderField($form, $field);
            }
        }
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

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $eventData = $event->getData();
        $formEntity = $form->getData();

        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');

        if ($form->isValid()) {

            //handle linked assets.
            $fileData = [];
            foreach ($sessionBag->getIterator() as $key => $sessionValue) {
                $formKey = 'file_' . $formEntity->getId();
                if (substr($key, 0, strlen($formKey)) !== $formKey) {
                    continue;
                }
                $fileData[$sessionValue['fieldName']][] = $sessionValue;
                $sessionBag->remove($key);
            }

            foreach ($fileData as $fieldName => $files) {
                $asset = $this->packageStream->createZipAsset($files, $formEntity->getName());
                if ($asset instanceof Asset) {
                    $hostUrl = \Pimcore\Tool::getHostUrl();
                    $eventData->$fieldName = $hostUrl . $asset->getRealFullPath();
                }
            }

            $event->setData($eventData);
        }
    }

    /**
     * @param FormInterface      $form
     * @param FormFieldInterface $field
     */
    private function addFormBuilderField(FormInterface $form, FormFieldInterface $field)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $constraints = [];
        foreach ($field->getConstraints() as $constraint) {

            if (!isset($this->availableConstraints[$constraint['type']])) {
                continue;
            }

            $class = $this->availableConstraints[$constraint['type']]['class'];
            $constraints[] = new $class();
        }

        if (!empty($constraints)) {
            $options['constraints'] = $constraints;
        }

        $form->add(
            $field->getName(),
            $this->availableFormTypes[$field->getType()]['class'],
            $options
        );
    }

    /**
     * @param FormInterface             $form
     * @param FormFieldDynamicInterface $field
     */
    private function addDynamicField(FormInterface $form, FormFieldDynamicInterface $field)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $form->add(
            $field->getName(),
            $field->getType(),
            $options
        );
    }
}