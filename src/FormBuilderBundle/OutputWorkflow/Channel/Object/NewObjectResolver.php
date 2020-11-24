<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Form\Data\FormDataInterface;
use Pimcore\Model\DataObject;

class NewObjectResolver extends AbstractObjectResolver
{
    /**
     * @var string
     */
    protected $resolvingObjectClass;

    /**
     * @var array
     */
    protected $storagePath;

    /**
     * @param string $resolvingObjectClass
     */
    public function setResolvingObjectClass(string $resolvingObjectClass)
    {
        $this->resolvingObjectClass = $resolvingObjectClass;
    }

    /**
     * @return mixed
     */
    public function getResolvingObjectClass()
    {
        return $this->resolvingObjectClass;
    }

    /**
     * @param array $storagePath
     */
    public function setStoragePath(array $storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * @return array
     */
    public function getStoragePath()
    {
        return $this->storagePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageObject()
    {
        /** @var FormDataInterface $formData */
        $formData = $this->getForm()->getData();

        $storageFolder = $this->getStorageFolder();
        $pathName = sprintf('\Pimcore\Model\DataObject\%s', ucfirst($this->getResolvingObjectClass()));

        /** @var DataObject\Concrete $object */
        $object = $this->modelFactory->build($pathName);

        $object->setParent($storageFolder);
        $object->setKey(uniqid(sprintf('form-%d-', $formData->getFormDefinition()->getId())));
        $object->setPublished(true);

        return $object;
    }

    /**
     * @return DataObject\Folder
     *
     * @throws \Exception
     */
    public function getStorageFolder()
    {
        $storageFolderInfo = $this->getStoragePath();
        $storageFolderId = $storageFolderInfo['id'];
        $storageFolder = DataObject\Folder::getById($storageFolderId);

        if (!$storageFolder instanceof DataObject\Folder) {
            throw new \Exception(sprintf('Storage Folder with id "%s" not found.', $storageFolderId));
        }

        return $storageFolder;
    }

    /**
     * {@inheritdoc}
     */
    public function fieldTypeAllowedToProcess($fieldType)
    {
        return true;
    }
}
