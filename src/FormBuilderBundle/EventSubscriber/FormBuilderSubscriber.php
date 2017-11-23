<?php

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\Form\PostSetDataEvent;
use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Storage\FormFieldDynamicInterface;
use FormBuilderBundle\Storage\FormInterface as FormBuilderFormInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Stream\PackageStream;
use FormBuilderBundle\Validation\ConstraintConnector;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var ConstraintConnector
     */
    protected $constraintConnector;

    /**
     * @var FormRegistry
     */
    protected $formRegistry;

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
     * @param Configuration            $configuration
     * @param PackageStream            $packageStream
     * @param EventDispatcherInterface $eventDispatcher
     * @param SessionInterface         $session
     * @param ConstraintConnector      $constraintConnector
     * @param FormRegistry $formRegistry
     */
    public function __construct(
        Configuration $configuration,
        PackageStream $packageStream,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        ConstraintConnector $constraintConnector,
        FormRegistry $formRegistry
    ) {
        $this->configuration = $configuration;
        $this->packageStream = $packageStream;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->constraintConnector = $constraintConnector;
        $this->formRegistry = $formRegistry;

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
    public function onPreSetData(FormEvent $event)
    {
        $preSetDataEvent = new PreSetDataEvent($event, $this->formOptions);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_PRE_SET_DATA, $preSetDataEvent);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        $postSetDataEvent = new PostSetDataEvent($event, $this->formOptions);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_POST_SET_DATA, $postSetDataEvent);

        $form = $event->getForm();
        $formEntity = $event->getData();
        $this->populateForm($form, $formEntity, TRUE);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $preSubmitEvent = new PreSubmitEvent($event, $this->formOptions);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_PRE_SUBMIT, $preSubmitEvent);

        $form = $event->getForm();
        $formEntity = $form->getData();
        $this->populateForm($form, $formEntity, FALSE, $event->getData());
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
     * @param FormInterface            $form
     * @param FormBuilderFormInterface $formEntity
     * @param bool                     $initial
     * @param array                    $data
     */
    private function populateForm(FormInterface $form, FormBuilderFormInterface $formEntity, $initial = FALSE, $data = [])
    {
        $orderedFields = $formEntity->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        /** @var FormFieldInterface $field */
        foreach ($orderedFields as $field) {
            if ($field instanceof FormFieldDynamicInterface) {
                // do not initialize dynamic fields twice since there is also no conditional logic!
                if ($initial === FALSE && !$field->isUpdated()) {
                    continue;
                }
                $this->addDynamicField($form, $field, $data);
            } else {
                // since we apply conditional logic here, we need to add fields multiple times (post-set-data and pre-submit). :(
                $this->addFormBuilderField($form, $field, $data);
            }
        }
    }

    /**
     * @param FormInterface      $form
     * @param FormFieldInterface $field
     * @param mixed              $formData
     */
    private function addFormBuilderField(FormInterface $form, FormFieldInterface $field, $formData = NULL)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();
        $object = $this->formRegistry->getType($this->availableFormTypes[$field->getType()]['class'])->getOptionsResolver();
        $availableOptions = $object->getDefinedOptions();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $constraints = [];
        if (in_array('constraints', $availableOptions)) {
            $constraints = $this->constraintConnector->connect(
                $formData,
                $field,
                $this->availableConstraints,
                $form->getData()->getConditionalLogic()
            );

            if (!empty($constraints)) {
                $options['constraints'] = $constraints;
            }
        }

        if (in_array('required', $availableOptions)) {
            $options['required'] = count(
                    array_filter($constraints, function ($constraint) {
                        return $constraint instanceof NotBlank;
                    })
                ) === 1;
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
     * @param mixed                     $formData
     */
    private function addDynamicField(FormInterface $form, FormFieldDynamicInterface $field, $formData = NULL)
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