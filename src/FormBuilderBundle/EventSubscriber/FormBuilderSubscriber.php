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
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
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
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var FormRegistryInterface
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
     * @param Dispatcher               $dispatcher
     * @param FormRegistryInterface    $formRegistry
     */
    public function __construct(
        Configuration $configuration,
        PackageStream $packageStream,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        Dispatcher $dispatcher,
        FormRegistryInterface $formRegistry
    ) {
        $this->configuration = $configuration;
        $this->packageStream = $packageStream;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
        $this->formRegistry = $formRegistry;
        $this->availableConstraints = $this->configuration->getAvailableConstraints();
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
        $this->populateForm($form, $formEntity, true);
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
        $this->populateForm($form, $formEntity, false, $event->getData());
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
    private function populateForm(FormInterface $form, FormBuilderFormInterface $formEntity, $initial = false, $data = [])
    {
        $orderedFields = $formEntity->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        $data = $this->prefillData($orderedFields, $data);

        /** @var FormFieldInterface $field */
        foreach ($orderedFields as $field) {
            if ($field instanceof FormFieldDynamicInterface) {
                // do not initialize dynamic fields twice since there is also no conditional logic!
                if ($initial === false && !$field->isUpdated()) {
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
     * @param null               $formData
     * @throws \Exception
     */
    private function addFormBuilderField(FormInterface $form, FormFieldInterface $field, $formData = null)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();
        $object = $this->formRegistry->getType($this->availableFormTypes[$field->getType()]['class'])->getOptionsResolver();
        $availableOptions = $object->getDefinedOptions();

        $constraints = [];
        $constraintNames = [];
        $templateClasses = [];

        //set optional template
        if (isset($optional['template'])) {
            $templateClasses[] = $optional['template'];
        }

        //tweak preferred choice options.
        $choiceTypes = ['choice', 'dynamic_choice', 'country'];
        if (in_array($field->getType(), $choiceTypes)) {
            if (
                $options['multiple'] === false
                && isset($options['data'])
                && is_array($options['data'])
                && !empty($options['data'])
            ) {
                $options['data'] = $options['data'][0];
            }
        }

        if (in_array('constraints', $availableOptions)) {

            $constraintData = $this->dispatcher->runFieldDispatcher('constraints', [
                'formData'         => $formData,
                'field'            => $field,
                'conditionalLogic' => $form->getData()->getConditionalLogic()
            ], [
                'availableConstraints' => $this->availableConstraints
            ]);

            // add field constraints to data attribute since we need them for the frontend cl applier.
            foreach ($field->getConstraints() as $constraint) {
                $constraintNames[] = $constraint['type'];
            }

            if ($constraintData->hasData()) {
                $options['constraints'] = $constraintData->getData();
            }
        }

        $options['attr']['data-initial-constraints'] = join(',', $constraintNames);

        if (in_array('required', $availableOptions)) {
            $options['required'] = count(
                    array_filter($constraints, function ($constraint) {
                        return $constraint instanceof NotBlank;
                    })
                ) === 1;
        }

        if (in_array('conditionalLogic', $availableOptions)) {
            //$options['conditionalLogic'] = NULL;
        }

        $classData = $this->dispatcher->runFieldDispatcher('form_type_classes', [
            'formData'         => $formData,
            'field'            => $field,
            'conditionalLogic' => $form->getData()->getConditionalLogic()
        ]);

        if ($classData->hasData()) {
            $templateClasses = array_merge($templateClasses, $classData->getData());
        }

        if (!empty($templateClasses)) {
            $options['attr']['data-template'] = join(' ', $templateClasses);
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
    private function addDynamicField(FormInterface $form, FormFieldDynamicInterface $field, $formData = null)
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

    /**
     * Add pre-filled data to value store
     *
     * @param $fields
     * @param $data
     * @return mixed
     */
    private function prefillData($fields, $data)
    {
        /** @var FormFieldInterface $field */
        foreach ($fields as $field) {

            if (!empty($data[$field->getName()])) {
                continue;
            }

            $fieldOptions = $field->getOptions();
            if (isset($fieldOptions['data'])) {
                $data[$field->getName()] = $fieldOptions['data'];
            }
        }

        return $data;
    }
}