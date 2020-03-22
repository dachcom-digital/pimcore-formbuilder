<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\ModelInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractObjectResolver
{
    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @var array
     */
    protected $storagePath;

    /**
     * @var array
     */
    protected $objectMappingData;

    /**
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param array                            $storagePath
     * @param array                            $objectMappingData
     */
    public function __construct(
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        array $storagePath,
        array $objectMappingData
    ) {
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->storagePath = $storagePath;
        $this->objectMappingData = $objectMappingData;
    }

    /**
     * @return DataObject\Concrete
     *
     * @throws \Exception
     */
    public abstract function getStorageObject();

    /**
     * @param string $fieldType
     *
     * @return bool
     */
    public abstract function fieldTypeAllowedToProcess($fieldType);

    /**
     * @return array
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * @return array
     */
    public function getObjectMappingData()
    {
        return $this->objectMappingData;
    }

    /**
     * @param FormInterface $form
     * @param string        $workflowName
     * @param string        $locale
     *
     * @throws \Exception
     */
    public function resolve(FormInterface $form, string $workflowName, string $locale)
    {
        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        $storageFolderInfo = $this->getStoragePath();
        $storageFolderId = $storageFolderInfo['id'];
        $storageFolder = DataObject\Folder::getById($storageFolderId);

        if (!$storageFolder instanceof DataObject\Folder) {
            throw new \Exception(sprintf('Storage Folder with id "%s" not found.', $storageFolderId));
        }

        $object = $this->getStorageObject();
        $object->setParent($storageFolder);

        // @todo: add object setup resolver (key, published)?
        $object->setKey(uniqid(sprintf('form-%d-', $formData->getFormDefinition()->getId())));
        $object->setPublished(true);

        $this->processObject($object, $form, $locale);

        $object->save();
    }

    /**
     * @param DataObject\Concrete $object
     * @param FormInterface       $form
     * @param string              $locale
     */
    protected function processObject(DataObject\Concrete $object, FormInterface $form, string $locale)
    {
        $definition = $this->getObjectMappingData();
        if (empty($definition)) {
            return;
        }

        $formData = $this->formValuesOutputApplier->applyForChannel($form, [], 'object', $locale);

        if (!is_array($formData)) {
            return;
        }

        $this->processObjectData($object, $formData);
    }

    /**
     * @param DataObject\Concrete $object
     * @param array               $formData
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
     */
    protected function appendToFieldCollection(DataObject\Concrete $object, string $fieldCollectionMethodName, array $workerData, array $containerFieldData)
    {
        $containerType = $containerFieldData['type'];
        $fieldCollectionType = $workerData['fieldCollectionClassKey'];
        $fieldMapping = $workerData['fieldMapping'];

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

            $objectFieldCollections->add($fieldCollection);
        }

        $object->$fieldCollectionSetter($objectFieldCollections);
    }

    /**
     * @param ModelInterface $object
     * @param array          $definition
     * @param mixed          $value
     */
    protected function assignChildDataToObject(ModelInterface $object, array $definition, $value)
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
     * @param ModelInterface $object
     * @param string         $methodName
     * @param mixed          $value
     */
    protected function appendToMethod(ModelInterface $object, string $methodName, $value)
    {
        $objectSetter = sprintf('set%s', ucfirst($methodName));

        if (!method_exists($object, $objectSetter)) {
            return;
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
                if (($subField = $this->findMapDefinition($definitionField['childs'], $formFieldName)) !== false) {
                    return $subField;
                }
            }
        }

        return false;
    }
}