<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Form\Data\FormDataInterface;
use Pimcore\Model\DataObject;

class NewObjectResolver extends AbstractObjectResolver
{
    protected ?string $resolvingObjectClass;
    protected array $storagePath;

    public function setResolvingObjectClass(string $resolvingObjectClass): void
    {
        $this->resolvingObjectClass = $resolvingObjectClass;
    }

    public function getResolvingObjectClass(): ?string
    {
        return $this->resolvingObjectClass;
    }

    public function setStoragePath(array $storagePath): void
    {
        $this->storagePath = $storagePath;
    }

    public function getStoragePath(): array
    {
        return $this->storagePath;
    }

    public function getStorageObject(): DataObject\Concrete
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

    public function getStorageFolder(): DataObject\Folder
    {
        $storageFolderInfo = $this->getStoragePath();
        $storageFolderId = $storageFolderInfo['id'];
        $storageFolder = DataObject\Folder::getById($storageFolderId);

        if (!$storageFolder instanceof DataObject\Folder) {
            throw new \Exception(sprintf('Storage Folder with id "%s" not found.', $storageFolderId));
        }

        return $storageFolder;
    }

    public function fieldTypeAllowedToProcess($fieldType): bool
    {
        return true;
    }
}
