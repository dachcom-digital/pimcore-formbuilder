<?php

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\Form\PostSetDataEvent;
use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
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
     * @var array
     */
    private $availableConstraints;

    /**
     * @var array
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
     * @param array $formOptions
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    private function populateForm(FormInterface $form, FormBuilderFormInterface $formEntity, $initial = false, array $data = [])
    {
        $orderedFields = $formEntity->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        $data = $this->preFillData($orderedFields, $data);

        /** @var FormFieldInterface $field */
        foreach ($orderedFields as $field) {
            if ($field instanceof FormFieldDynamicInterface) {
                // do not initialize dynamic fields twice since there is also no conditional logic!
                if ($initial === false && !$field->isUpdated()) {
                    continue;
                }
                $formTypeData = $this->addDynamicField($field);
                $form->add($formTypeData['name'], $formTypeData['type'], $formTypeData['options']);
            } else {
                // since we apply conditional logic here
                // we need to add fields multiple times (post-set-data and pre-submit). :(
                if ($field instanceof FormFieldContainerInterface) {
                    $formConditionalLogic = $form->getRoot()->getData()->getConditionalLogic();
                    $subFieldData = isset($data[$field->getName()]) ? $data[$field->getName()] : [];
                    $formTypeData = $this->addFormBuilderContainerField($field, $subFieldData, $formConditionalLogic);
                    $form->add($formTypeData['name'], $formTypeData['type'], $formTypeData['options']);
                } else {
                    $formConditionalLogic = $form->getRoot()->getData()->getConditionalLogic();
                    $formTypeData = $this->addFormBuilderField($field, $data, $formConditionalLogic);
                    $form->add($formTypeData['name'], $formTypeData['type'], $formTypeData['options']);
                }
            }
        }
    }

    /**
     * @param FormFieldContainerInterface $fieldContainer
     * @param array                       $formData
     * @param array                       $formConditionalLogic
     *
     * @return array
     *
     * @throws \Exception
     */
    private function addFormBuilderContainerField(FormFieldContainerInterface $fieldContainer, array $formData, array $formConditionalLogic)
    {
        $fields = [];
        foreach ($fieldContainer->getFields() as $subField) {
            $fields[] = $this->addFormBuilderField($subField, $formData, $formConditionalLogic);
        }

        $typeClass = $this->configuration->getContainerFieldClass($fieldContainer->getSubType());

        $data = [
            'name'    => $fieldContainer->getName(),
            'type'    => $typeClass,
            'options' => [
                'formbuilder_configuration' => $fieldContainer->getConfiguration(),
                'entry_options'             => [
                    'fields'         => $fields,
                    'container_type' => $fieldContainer->getSubType()
                ],
                'attr'                      => [
                    'class' => 'formbuilder-container formbuilder-container-' . strtolower($fieldContainer->getSubType())
                ]
            ]
        ];

        return $data;
    }

    /**
     * @param FormFieldInterface $field
     * @param array              $formData
     * @param array              $formConditionalLogic
     *
     * @return array
     *
     * @throws \Exception
     */
    private function addFormBuilderField(FormFieldInterface $field, array $formData, array $formConditionalLogic)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();
        $object = $this->formRegistry->getType($this->availableFormTypes[$field->getType()]['class'])->getOptionsResolver();
        $availableOptions = $object->getDefinedOptions();

        $constraints = [];
        $constraintNames = [];
        $templateClasses = [];

        // options enrichment:  tweak preferred choice options
        if (in_array($field->getType(), $this->getChoiceFieldTypes())) {
            if ($options['multiple'] === false
                && isset($options['data'])
                && is_array($options['data'])
                && !empty($options['data'])
            ) {
                $options['data'] = $options['data'][0];
            }
        }

        // options enrichment: add constraints
        if (in_array('constraints', $availableOptions)) {
            $constraintData = $this->dispatcher->runFieldDispatcher('constraints', [
                'formData'         => $formData,
                'field'            => $field,
                'conditionalLogic' => $formConditionalLogic
            ], [
                'availableConstraints' => $this->availableConstraints
            ]);

            // add field constraints to data attribute since we need them for the frontend cl applier.
            foreach ($field->getConstraints() as $constraint) {
                $constraintNames[] = $constraint['type'];
            }

            if ($constraintData->hasData()) {
                $constraints = $constraintData->getData();
                $options['constraints'] = $constraints;
            }
        }

        $options['attr']['data-initial-constraints'] = join(',', $constraintNames);

        // options enrichment: check required state
        if (in_array('required', $availableOptions)) {
            $options['required'] = count(
                array_filter($constraints, function ($constraint) {
                    return $constraint instanceof NotBlank;
                })
            ) === 1;
        }

        // options enrichment: check for custom radio / checkbox layout
        if ($this->configuration->getConfigFlag('use_custom_radio_checkbox') === true) {
            if (in_array('label_attr', $availableOptions)) {
                if (in_array($field->getType(), ['checkbox'])) {
                    $options['label_attr'] = ['class' => 'checkbox-custom'];
                } elseif (in_array($field->getType(), $this->getChoiceFieldTypes())) {
                    if ($options['expanded'] === true) {
                        $options['label_attr'] = ['class' => $options['multiple'] === true ? 'checkbox-custom' : 'radio-custom'];
                    }
                }
            }
        }

        // options enrichment: set template
        if (isset($optional['template'])) {
            $templateClasses[] = $optional['template'];
        }

        // options enrichment: conditional logic class mapping
        $classData = $this->dispatcher->runFieldDispatcher('form_type_classes', [
            'formData'         => $formData,
            'field'            => $field,
            'conditionalLogic' => $formConditionalLogic
        ]);

        if ($classData->hasData()) {
            $templateClasses = array_merge($templateClasses, $classData->getData());
        }

        if (!empty($templateClasses)) {
            $options['attr']['data-template'] = join(' ', $templateClasses);
        }

        $data = [
            'name'    => $field->getName(),
            'type'    => $this->availableFormTypes[$field->getType()]['class'],
            'options' => $options
        ];

        return $data;
    }

    /**
     * @param FormFieldDynamicInterface $field
     *
     * @return array
     */
    private function addDynamicField(FormFieldDynamicInterface $field)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $data = [
            'name'    => $field->getName(),
            'type'    => $field->getType(),
            'options' => $options
        ];

        return $data;
    }

    /**
     * @return array
     */
    private function getChoiceFieldTypes()
    {
        return ['choice', 'dynamic_choice', 'country'];
    }

    /**
     * Add pre-filled data to value store.
     *
     * @param array $fields
     * @param array $data
     *
     * @return array
     */
    private function preFillData(array $fields, array &$data)
    {
        /** @var FormFieldInterface|FormFieldContainerInterface $field */
        foreach ($fields as $field) {
            if (!empty($data[$field->getName()])) {
                continue;
            }

            if ($field instanceof FormFieldContainerInterface) {
                if (!isset($data[$field->getName()])) {
                    $data[$field->getName()] = [];
                }
                $this->preFillData($field->getFields(), $data[$field->getName()]);

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
