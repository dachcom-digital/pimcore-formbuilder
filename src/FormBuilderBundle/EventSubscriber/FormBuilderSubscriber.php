<?php

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Validator\Constraints\DynamicMultiFileNotBlank;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Event\Form\PostSetDataEvent;
use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormBuilderSubscriber implements EventSubscriberInterface
{
    protected Configuration $configuration;
    protected EventDispatcherInterface $eventDispatcher;
    protected Dispatcher $dispatcher;
    protected FormRegistryInterface $formRegistry;

    private array $availableConstraints;
    private array $availableFormTypes;

    public function __construct(
        Configuration $configuration,
        EventDispatcherInterface $eventDispatcher,
        Dispatcher $dispatcher,
        FormRegistryInterface $formRegistry
    ) {
        $this->configuration = $configuration;
        $this->eventDispatcher = $eventDispatcher;
        $this->dispatcher = $dispatcher;
        $this->formRegistry = $formRegistry;
        $this->availableConstraints = $this->configuration->getAvailableConstraints();
        $this->availableFormTypes = $this->configuration->getConfig('types');
    }

    /**
     * @param FormEvent $event
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getFormOptions(FormEvent $event)
    {
        $form = $event->getForm();

        if (!$form->has('formRuntimeData')) {
            throw new \Exception('No runtime options in form found.');
        }

        $data = $form->get('formRuntimeData')->getData();

        // remove legacy email config node.
        if (isset($data['email'])) {
            unset($data['email']);
        }

        return $data;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => ['onPreSetData'],
            FormEvents::POST_SET_DATA => ['onPostSetData'],
            FormEvents::PRE_SUBMIT    => ['onPreSubmit']
        ];
    }

    /**
     * @param FormEvent $event
     *
     * @throws \Exception
     */
    public function onPreSetData(FormEvent $event)
    {
        $preSetDataEvent = new PreSetDataEvent($event, $this->getFormOptions($event));
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_PRE_SET_DATA, $preSetDataEvent);
    }

    /**
     * @param FormEvent $event
     *
     * @throws \Exception
     */
    public function onPostSetData(FormEvent $event)
    {
        $postSetDataEvent = new PostSetDataEvent($event, $this->getFormOptions($event));
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_POST_SET_DATA, $postSetDataEvent);

        $this->populateForm($event->getForm(), $event->getData());
    }

    /**
     * @param FormEvent $event
     *
     * @throws \Exception
     */
    public function onPreSubmit(FormEvent $event)
    {
        $preSubmitEvent = new PreSubmitEvent($event, $this->getFormOptions($event));
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_PRE_SUBMIT, $preSubmitEvent);

        $this->populateForm($event->getForm(), $event->getForm()->getData(), $event->getData());
    }

    /**
     * @param FormInterface     $form
     * @param FormDataInterface $formData
     * @param array             $data
     *
     * @throws \Exception
     */
    private function populateForm(FormInterface $form, FormDataInterface $formData, array $data = [])
    {
        $orderedFields = $formData->getFormDefinition()->getFields();
        usort($orderedFields, function (FieldDefinitionInterface $a, FieldDefinitionInterface $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        $data = $this->preFillData($orderedFields, $data);
        $formRuntimeOptions = !$form->has('formRuntimeData') ? [] : $form->get('formRuntimeData')->getData();

        $conditionalLogicBaseOptions = [
            'formRuntimeOptions' => $formRuntimeOptions,
            'conditionalLogic'   => $formData->getFormDefinition()->getConditionalLogic()
        ];

        foreach ($orderedFields as $field) {
            if ($field instanceof FormFieldDynamicDefinitionInterface) {
                $formTypeData = $this->addDynamicField($field);
                $form->add($formTypeData['name'], $formTypeData['type'], $formTypeData['options']);
            } elseif ($field instanceof FormFieldContainerDefinitionInterface) {
                $subFieldData = isset($data[$field->getName()]) ? $data[$field->getName()] : [];
                $conditionalLogicOptions = array_merge(['formData' => $subFieldData, 'field' => null], $conditionalLogicBaseOptions);
                $formTypeData = $this->addFormBuilderContainerField($field, $conditionalLogicOptions);
                $form->add($formTypeData['name'], $formTypeData['type'], $formTypeData['options']);
            } else {
                $conditionalLogicOptions = array_merge(['formData' => $data, 'field' => $field], $conditionalLogicBaseOptions);
                $formTypeData = $this->addFormBuilderField($field, $conditionalLogicOptions);
                $form->add($formTypeData['name'], $formTypeData['type'], $formTypeData['options']);
            }
        }
    }

    /**
     * @param FormFieldContainerDefinitionInterface $fieldContainer
     * @param array                                 $conditionalLogicOptions
     *
     * @return array
     *
     * @throws \Exception
     */
    private function addFormBuilderContainerField(FormFieldContainerDefinitionInterface $fieldContainer, array $conditionalLogicOptions)
    {
        $fields = [];
        foreach ($fieldContainer->getFields() as $subField) {
            $fields[] = $this->addFormBuilderField($subField, array_merge($conditionalLogicOptions, ['field' => $subField]));
        }

        $typeClass = $this->configuration->getContainerFieldClass($fieldContainer->getSubType());
        $configuration = $fieldContainer->getConfiguration();

        $containerAttributes = isset($configuration['attr']) ? $configuration['attr'] : [];

        $containerClasses = ['formbuilder-container formbuilder-container-' . strtolower($fieldContainer->getSubType())];
        if (isset($containerAttributes['class']) && is_string($containerAttributes['class'])) {
            $containerClasses[] = $containerAttributes['class'];
        }

        // merge core and attributes class definition
        $containerAttributes['class'] = join(' ', $containerClasses);

        // options enrichment: conditional logic class mapping
        $conditionalContainerClassData = $this->dispatchConditionalLogicModule('form_type_classes', array_merge($conditionalLogicOptions, ['field' => $fieldContainer]));

        if ($conditionalContainerClassData->hasData()) {
            $attrDataTemplate = isset($containerAttributes['data-template']) ? [$containerAttributes['data-template']] : [];
            $attrDataTemplate = array_merge($attrDataTemplate, $conditionalContainerClassData->getData());
            $containerAttributes['data-template'] = join(' ', $attrDataTemplate);
        }

        return [
            'name'    => $fieldContainer->getName(),
            'type'    => $typeClass,
            'options' => [
                'attr'                      => $containerAttributes,
                'formbuilder_configuration' => $configuration,
                'entry_options'             => [
                    'fields'         => $fields,
                    'container_type' => $fieldContainer->getSubType()
                ]
            ]
        ];
    }

    /**
     * @param FormFieldDefinitionInterface $field
     * @param array                        $conditionalLogicOptions
     *
     * @return array
     *
     * @throws \Exception
     */
    private function addFormBuilderField(FormFieldDefinitionInterface $field, array $conditionalLogicOptions)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();
        $object = $this->formRegistry->getType($this->availableFormTypes[$field->getType()]['class'])->getOptionsResolver();
        $availableOptions = $object->getDefinedOptions();

        $constraints = [];
        $constraintNames = [];
        $templateClasses = [];

        // options enrichment: tweak preferred choice options
        if (in_array($field->getType(), $this->getChoiceFieldTypes())) {
            if (isset($options['multiple']) && $options['multiple'] === false
                && isset($options['data'])
                && is_array($options['data'])
                && !empty($options['data'])
            ) {
                $options['data'] = $options['data'][0];
            }
        }

        // options enrichment: add constraints
        if (in_array('constraints', $availableOptions)) {
            $conditionalConstraintData = $this->dispatchConditionalLogicModule('constraints', $conditionalLogicOptions);

            // add field constraints to data attribute since we need them for the frontend cl applier.
            foreach ($field->getConstraints() as $constraint) {
                $constraintNames[] = $constraint['type'];
            }

            if ($conditionalConstraintData->hasData()) {
                $constraints = $conditionalConstraintData->getData();
                $options['constraints'] = $constraints;
            }
        }

        $options['attr']['data-initial-constraints'] = join(',', $constraintNames);

        // options enrichment: check required state
        if (in_array('required', $availableOptions)) {
            $options['required'] = count(
                    array_filter($constraints, function ($constraint) {
                        return $constraint instanceof NotBlank || $constraint instanceof DynamicMultiFileNotBlank;
                    })
                ) === 1;
        }

        // options enrichment: check for custom radio / checkbox layout
        if ($this->configuration->getConfigFlag('use_custom_radio_checkbox') === true) {
            if (in_array('label_attr', $availableOptions)) {
                if (in_array($field->getType(), ['checkbox'])) {
                    $options['label_attr'] = ['class' => 'checkbox-custom'];
                } elseif (in_array($field->getType(), $this->getChoiceFieldTypes())) {
                    if (isset($options['expanded']) && $options['expanded'] === true) {
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
        $conditionalClassData = $this->dispatchConditionalLogicModule('form_type_classes', $conditionalLogicOptions);

        if ($conditionalClassData->hasData()) {
            $templateClasses = array_merge($templateClasses, $conditionalClassData->getData());
        }

        if (!empty($templateClasses)) {
            $options['attr']['data-template'] = implode(' ', $templateClasses);
        }

        return [
            'name'    => $field->getName(),
            'type'    => $this->availableFormTypes[$field->getType()]['class'],
            'options' => $options
        ];
    }

    /**
     * @param string $dispatcherModule
     * @param array  $options
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    private function dispatchConditionalLogicModule(string $dispatcherModule, array $options)
    {
        $moduleOptions = [];

        if ($dispatcherModule === 'constraints') {
            $moduleOptions = [
                'availableConstraints' => $this->availableConstraints
            ];
        }

        return $this->dispatcher->runFieldDispatcher($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @param FormFieldDynamicDefinitionInterface $field
     *
     * @return array
     */
    private function addDynamicField(FormFieldDynamicDefinitionInterface $field)
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        return [
            'name'    => $field->getName(),
            'type'    => $field->getType(),
            'options' => $options
        ];
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
        /** @var FormFieldDefinitionInterface $field */
        foreach ($fields as $field) {
            if (!empty($data[$field->getName()])) {
                continue;
            }

            if ($field instanceof FormFieldContainerDefinitionInterface) {
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
