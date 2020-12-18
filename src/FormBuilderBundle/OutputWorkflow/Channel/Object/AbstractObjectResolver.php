<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use Pimcore\Model\DataObject;
use Pimcore\Model\FactoryInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\OutputWorkflow\Channel\Object\Helper\FieldCollectionValidationHelper;
use FormBuilderBundle\Transformer\Target\TargetAwareOutputTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractObjectResolver
{
    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $objectMappingData;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var array
     */
    protected $formRuntimeData;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $workflowName;

    /**
     * @var FactoryInterface
     */
    protected $modelFactory;

    /**
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param EventDispatcherInterface         $eventDispatcher
     * @param FactoryInterface                 $modelFactory
     * @param array                            $objectMappingData
     */
    public function __construct(
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        EventDispatcherInterface $eventDispatcher,
        FactoryInterface $modelFactory,
        array $objectMappingData
    ) {
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelFactory = $modelFactory;
        $this->objectMappingData = $objectMappingData;
    }

    /**
     * @return DataObject\Concrete
     *
     * @throws \Exception
     */
    abstract public function getStorageObject();

    /**
     * @param string $fieldType
     *
     * @return bool
     */
    abstract public function fieldTypeAllowedToProcess($fieldType);

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param array $formRuntimeData
     */
    public function setFormRuntimeData(array $formRuntimeData)
    {
        $this->formRuntimeData = $formRuntimeData;
    }

    /**
     * @return array
     */
    public function getFormRuntimeData()
    {
        return $this->formRuntimeData;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $workflowName
     */
    public function setWorkflowName(string $workflowName)
    {
        $this->workflowName = $workflowName;
    }

    /**
     * @return string
     */
    public function getWorkflowName()
    {
        return $this->workflowName;
    }

    /**
     * @return array
     */
    public function getObjectMappingData()
    {
        return $this->objectMappingData;
    }

    /**
     * @throws \Exception
     */
    public function resolve()
    {
        $object = $this->getStorageObject();

        if (null === $object = $this->dispatchGuardEvent($object)) {
            return;
        }

        $this->processObject($object);

        // disable mandatory check!
        $object->setOmitMandatoryCheck(true);
        $object->save();
    }

    /**
     * @param DataObject\Concrete $object
     *
     * @throws GuardException
     */
    protected function processObject(DataObject\Concrete $object)
    {
        $definition = $this->getObjectMappingData();
        if (empty($definition)) {
            return;
        }

        $formData = $this->formValuesOutputApplier->applyForChannel($this->getForm(), [], 'object', $this->getLocale());

        if (!is_array($formData)) {
            return;
        }

        $this->processObjectData($object, $formData);
    }

    /**
     * @param DataObject\Concrete $object
     * @param array               $formData
     *
     * @throws GuardException
     */
    protected function processObjectData(DataObject\Concrete $object, array $formData)
    {
        foreach ($formData as $fieldData) {
            if ($this->fieldTypeAllowedToProcess($fieldData['field_type']) === false) {
                continue;
            }

            if ($fieldData['field_type'] === 'container' && count($fieldData['fields']) > 0) {
                $this->mapContainerField($object, $fieldData);
            } else {
                $this->mapField($object, $fieldData);
            }
        }
    }

    /**
     * @param DataObject\Concrete $object
     * @param array               $fieldData
     */
    protected function mapField(DataObject\Concrete $object, array $fieldData)
    {
        $fieldName = $fieldData['name'];
        $fieldValue = $fieldData['value'];

        $fieldDefinition = $this->findMapDefinition($this->getObjectMappingData(), $fieldName);

        if ($fieldDefinition === false) {
            return;
        }

        $this->assignChildDataToObject($object, $fieldDefinition, $fieldValue);
    }

    /**
     * @param DataObject\Concrete $object
     * @param array               $containerFieldData
     *
     * @throws GuardException
     */
    protected function mapContainerField(DataObject\Concrete $object, array $containerFieldData)
    {
        $fieldName = $containerFieldData['name'];

        $fieldDefinition = $this->findMapDefinition($this->getObjectMappingData(), $fieldName);

        if ($fieldDefinition === false) {
            return;
        }

        if (!is_array($fieldDefinition['childs'])) {
            return;
        }

        // there could be more than just one data field assignment
        foreach ($fieldDefinition['childs'] as $formDefinitionChild) {
            if ($formDefinitionChild['type'] !== 'data_class_field') {
                continue;
            }

            if (!isset($formDefinitionChild['config']['workerData'])) {
                continue;
            }

            if (!isset($formDefinitionChild['config']['worker'])) {
                continue;
            }

            // for now there can be a field-collection worker only!
            if ($formDefinitionChild['config']['worker'] !== 'fieldCollectionWorker') {
                continue;
            }

            $fieldCollectionStorageName = $formDefinitionChild['config']['name'];
            $workerData = $formDefinitionChild['config']['workerData'];
            if (!isset($workerData['fieldCollectionClassKey'])) {
                continue;
            }

            if (!isset($workerData['fieldMapping'])) {
                continue;
            }

            $this->appendToFieldCollection($object, $fieldCollectionStorageName, $workerData, $containerFieldData);
        }
    }

    /**
     * @param DataObject\Concrete $object
     * @param string              $fieldCollectionMethodName
     * @param array               $workerData
     * @param array               $containerFieldData
     *
     * @throws GuardException
     */
    protected function appendToFieldCollection(DataObject\Concrete $object, string $fieldCollectionMethodName, array $workerData, array $containerFieldData)
    {
        $fieldCollectionType = $workerData['fieldCollectionClassKey'];
        $fieldMapping = $workerData['fieldMapping'];
        $validationData = $workerData['validationData'];

        $fieldCollectionPath = sprintf('\Pimcore\Model\DataObject\Fieldcollection\Data\%s', ucfirst($fieldCollectionType));

        if (!class_exists($fieldCollectionPath)) {
            return;
        }

        $fieldCollectionSetter = sprintf('set%s', $fieldCollectionMethodName);
        $fieldCollectionGetter = sprintf('get%s', $fieldCollectionMethodName);

        if (!method_exists($object, $fieldCollectionSetter)) {
            return;
        }

        if (!method_exists($object, $fieldCollectionGetter)) {
            return;
        }

        $objectFieldCollections = $object->$fieldCollectionGetter();

        if (!$objectFieldCollections instanceof DataObject\Fieldcollection) {
            $objectFieldCollections = new DataObject\Fieldcollection();
        }

        // cycle to each repeater / field-collection block
        foreach ($containerFieldData['fields'] as $containerFieldDataBlock) {
            // every block needs a field-collection
            $fieldCollection = new $fieldCollectionPath();

            // now append each field to the field collection - if available in definition!
            foreach ($containerFieldDataBlock as $containerFieldDataBlockItem) {
                $fieldType = $containerFieldDataBlockItem['field_type'];
                $fieldName = $containerFieldDataBlockItem['name'];
                $fieldValue = $containerFieldDataBlockItem['value'];

                // currently not possible, but maybe there will be a nested container in the near future.
                if ($fieldType === 'container') {
                    $this->processObjectData($object, $containerFieldDataBlockItem);

                    continue;
                }

                $fieldDefinition = $this->findMapDefinition($fieldMapping, $fieldName);

                if ($fieldDefinition === false) {
                    return;
                }

                $this->assignChildDataToObject($fieldCollection, $fieldDefinition, $fieldValue);
            }

            if (null === $fieldCollection = $this->dispatchGuardEvent($fieldCollection)) {
                continue;
            }

            $validator = new FieldCollectionValidationHelper($validationData);
            $validator->validate($object, $objectFieldCollections, $fieldCollection);

            $objectFieldCollections->add($fieldCollection);
        }

        $object->$fieldCollectionSetter($objectFieldCollections);
    }

    /**
     * @param mixed $object
     * @param array $definition
     * @param mixed $value
     */
    protected function assignChildDataToObject($object, array $definition, $value)
    {
        if (!is_array($definition['childs'])) {
            return;
        }

        // there could be more than just one data field assignment
        foreach ($definition['childs'] as $formDefinitionChild) {
            if ($formDefinitionChild['type'] !== 'data_class_field') {
                continue;
            }

            $formDefinitionChildName = $formDefinitionChild['config']['name'];
            $this->appendToMethod($object, $formDefinitionChildName, $value);
        }
    }

    /**
     * @param mixed  $object
     * @param string $fieldName
     * @param mixed  $value
     */
    protected function appendToMethod($object, string $fieldName, $value)
    {
        $objectSetter = sprintf('set%s', ucfirst($fieldName));

        if (!method_exists($object, $objectSetter)) {
            return;
        }

        if ($value instanceof TargetAwareOutputTransformer) {
            $fieldDefinition = $this->getObjectFieldDefinition($object, $fieldName);
            $value = $value->transform($fieldDefinition);
        }

        $object->$objectSetter($value);
    }

    /**
     * @param array  $definitionFields
     * @param string $formFieldName
     *
     * @return bool|array
     */
    protected function findMapDefinition(array $definitionFields, $formFieldName)
    {
        foreach ($definitionFields as $definitionField) {
            if ($definitionField['type'] === 'form_field' && $definitionField['config']['name'] === $formFieldName) {
                return $definitionField;
            }

            if (isset($definitionField['childs']) && is_array($definitionField['childs'])) {
                if (false !== $subField = $this->findMapDefinition($definitionField['childs'], $formFieldName)) {
                    return $subField;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $subject
     *
     * @return DataObject\Fieldcollection\Data\AbstractData|DataObject\Concrete|null
     *
     * @throws GuardException
     */
    protected function dispatchGuardEvent($subject)
    {
        $channelSubjectGuardEvent = new ChannelSubjectGuardEvent($this->getForm()->getData(), $subject, $this->getWorkflowName(), 'object', $this->getFormRuntimeData());
        $this->eventDispatcher->dispatch(FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH, $channelSubjectGuardEvent);

        if ($channelSubjectGuardEvent->isSuspended()) {
            return null;
        }

        if ($channelSubjectGuardEvent->shouldStopChannel()) {
            throw new GuardChannelException($channelSubjectGuardEvent->getFailMessage());
        } elseif ($channelSubjectGuardEvent->shouldStopOutputWorkflow()) {
            throw new GuardOutputWorkflowException($channelSubjectGuardEvent->getFailMessage());
        }

        return $channelSubjectGuardEvent->getSubject();
    }

    /**
     * @param mixed  $object
     * @param string $fieldName
     *
     * @return DataObject\ClassDefinition\Data|null
     */
    protected function getObjectFieldDefinition($object, $fieldName)
    {
        if ($object instanceof DataObject\Concrete) {
            $classDefinition = $object->getClass();
            if ($classDefinition instanceof DataObject\ClassDefinition) {
                return $classDefinition->getFieldDefinition($fieldName);
            }
        } elseif ($object instanceof DataObject\Fieldcollection\Data\AbstractData) {
            $classDefinition = $object->getDefinition();
            if ($classDefinition instanceof DataObject\Fieldcollection\Definition) {
                return $classDefinition->getFieldDefinition($fieldName);
            }
        }

        return null;
    }
}
