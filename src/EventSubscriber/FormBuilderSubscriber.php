<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\Form\FormTypeOptionsEvent;
use FormBuilderBundle\Event\Form\PostSetDataEvent;
use FormBuilderBundle\Event\Form\PreSetDataEvent;
use FormBuilderBundle\Event\Form\PreSubmitEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use FormBuilderBundle\Registry\DataInjectionRegistry;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validator\Constraints\DynamicMultiFileNotBlank;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FormBuilderSubscriber implements EventSubscriberInterface
{
    private array $availableConstraints;
    private array $availableFormTypes;

    public function __construct(
        protected Configuration $configuration,
        protected EventDispatcherInterface $eventDispatcher,
        protected Dispatcher $dispatcher,
        protected FormRegistryInterface $formRegistry,
        protected DataInjectionRegistry $dataInjectionRegistry
    ) {
        $this->availableConstraints = $this->configuration->getAvailableConstraints();
        $this->availableFormTypes = $this->configuration->getConfig('types');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA  => ['onPreSetData'],
            FormEvents::POST_SET_DATA => ['onPostSetData'],
            FormEvents::PRE_SUBMIT    => ['onPreSubmit']
        ];
    }

    /**
     * @throws \Exception
     */
    public function getFormOptions(FormEvent $event): ?array
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
     * @throws \Exception
     */
    public function onPreSetData(FormEvent $event): void
    {
        $preSetDataEvent = new PreSetDataEvent($event, $this->getFormOptions($event));
        $this->eventDispatcher->dispatch($preSetDataEvent, FormBuilderEvents::FORM_PRE_SET_DATA);
    }

    /**
     * @throws \Exception
     */
    public function onPostSetData(FormEvent $event): void
    {
        $postSetDataEvent = new PostSetDataEvent($event, $this->getFormOptions($event));
        $this->eventDispatcher->dispatch($postSetDataEvent, FormBuilderEvents::FORM_POST_SET_DATA);

        $this->populateForm($event->getForm(), $event->getData());
    }

    /**
     * @throws \Exception
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $preSubmitEvent = new PreSubmitEvent($event, $this->getFormOptions($event));
        $this->eventDispatcher->dispatch($preSubmitEvent, FormBuilderEvents::FORM_PRE_SUBMIT);

        $this->populateForm($event->getForm(), $event->getForm()->getData(), $event->getData());
    }

    /**
     * @throws \Exception
     */
    private function populateForm(FormInterface $form, FormDataInterface $formData, array $data = []): void
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
                $conditionalLogicOptions = array_merge(['formData' => $data, 'field' => null], $conditionalLogicBaseOptions);
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
     * @throws \Exception
     */
    private function addFormBuilderContainerField(FormFieldContainerDefinitionInterface $fieldContainer, array $conditionalLogicOptions): array
    {
        $fields = [];
        foreach ($fieldContainer->getFields() as $subField) {
            $fields[] = $this->addFormBuilderField($subField, array_merge($conditionalLogicOptions, ['field' => $subField]));
        }

        $typeClass = $this->configuration->getContainerFieldClass($fieldContainer->getSubType());
        $configuration = $fieldContainer->getConfiguration();

        $containerAttributes = $configuration['attr'] ?? [];

        $containerClasses = ['formbuilder-container formbuilder-container-' . strtolower($fieldContainer->getSubType())];
        if (isset($containerAttributes['class']) && is_string($containerAttributes['class'])) {
            $containerClasses[] = $containerAttributes['class'];
        }

        // merge core and attributes class definition
        $containerAttributes['class'] = implode(' ', $containerClasses);

        // options enrichment: conditional logic class mapping
        $conditionalContainerClassData = $this->dispatchConditionalLogicModule('form_type_classes', array_merge($conditionalLogicOptions, ['field' => $fieldContainer]));

        if ($conditionalContainerClassData->hasData()) {
            $attrDataTemplate = isset($containerAttributes['data-template']) ? [$containerAttributes['data-template']] : [];
            $attrDataTemplate = array_merge($attrDataTemplate, $conditionalContainerClassData->getData());
            $containerAttributes['data-template'] = implode(' ', $attrDataTemplate);
        }

        $name = $fieldContainer->getName();
        $type = $typeClass;

        $options = [
            'attr'                      => $containerAttributes,
            'formbuilder_configuration' => $configuration,
            'entry_options'             => [
                'fields'         => $fields,
                'container_type' => $fieldContainer->getSubType()
            ]
        ];

        return [
            'name'    => $name,
            'type'    => $type,
            'options' => $this->dispatchFormTypeOptionsEvent($name, $type, $options)
        ];
    }

    /**
     * @throws \Exception
     */
    private function addFormBuilderField(FormFieldDefinitionInterface $field, array $conditionalLogicOptions): array
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();
        $object = $this->formRegistry->getType($this->availableFormTypes[$field->getType()]['class'])->getOptionsResolver();
        $availableOptions = $object->getDefinedOptions();

        $constraints = [];
        $constraintNames = [];
        $templateClasses = [];

        // options enrichment: tweak preferred choice options
        if (in_array($field->getType(), $this->getChoiceFieldTypes(), true)) {
            if (isset($options['multiple']) && $options['multiple'] === false && array_key_exists('data', $options) && is_array($options['data'])) {
                $options['data'] = $options['data'][0];
            }
        }

        if (array_key_exists('dataInjection', $options) && !empty($options['dataInjection'])) {
            $dataInjection = json_decode($options['dataInjection'], true);
            unset($options['dataInjection']);
            if ($this->dataInjectionRegistry->has($dataInjection['injector'])) {
                $options['data'] = $this->dataInjectionRegistry->get($dataInjection['injector'])->parseData($dataInjection['config']);
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

        $options['attr']['data-initial-constraints'] = implode(',', $constraintNames);

        // options enrichment: check required state
        if (in_array('required', $availableOptions)) {
            $options['required'] = count(
                array_filter($constraints, static function ($constraint) {
                    return $constraint instanceof NotBlank || $constraint instanceof DynamicMultiFileNotBlank;
                })
            ) === 1;
        }

        // options enrichment: check for custom radio / checkbox layout
        if ($this->configuration->getConfigFlag('use_custom_radio_checkbox') === true) {
            if (in_array('label_attr', $availableOptions)) {
                if ($field->getType() === 'checkbox') {
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

        $name = $field->getName();
        $type = $this->availableFormTypes[$field->getType()]['class'];

        return [
            'name'    => $name,
            'type'    => $type,
            'options' => $this->dispatchFormTypeOptionsEvent($name, $type, $options)
        ];
    }

    private function dispatchFormTypeOptionsEvent(string $name, string $type, array $options): array
    {
        $event = new FormTypeOptionsEvent($name, $type, $options);

        $this->eventDispatcher->dispatch($event, FormBuilderEvents::FORM_TYPE_OPTIONS);

        return $event->getOptions();
    }

    /**
     * @throws \Exception
     */
    private function dispatchConditionalLogicModule(string $dispatcherModule, array $options): DataInterface
    {
        $moduleOptions = [];

        if ($dispatcherModule === 'constraints') {
            $moduleOptions = [
                'availableConstraints' => $this->availableConstraints
            ];
        }

        return $this->dispatcher->runFieldDispatcher($dispatcherModule, $options, $moduleOptions);
    }

    private function addDynamicField(FormFieldDynamicDefinitionInterface $field): array
    {
        $options = $field->getOptions();
        $optional = $field->getOptional();

        //set optional template
        if (isset($optional['template'])) {
            $options['attr']['data-template'] = $optional['template'];
        }

        $name = $field->getName();
        $type = $field->getType();

        return [
            'name'    => $name,
            'type'    => $type,
            'options' => $this->dispatchFormTypeOptionsEvent($name, $type, $options)
        ];
    }

    private function getChoiceFieldTypes(): array
    {
        return ['choice', 'dynamic_choice', 'country'];
    }

    private function preFillData(array $fields, array &$data): array
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
