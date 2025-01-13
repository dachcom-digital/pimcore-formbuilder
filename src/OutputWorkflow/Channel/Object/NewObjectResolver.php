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

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Form\Data\FormDataInterface;
use Pimcore\Model\DataObject;

class NewObjectResolver extends AbstractObjectResolver
{
    protected ?string $resolvingObjectClass;
    protected array $storagePath;

    public function setResolvingObjectClass(?string $resolvingObjectClass): void
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

        if ($this->getDynamicObjectResolver() !== null) {
            $resolver = $this->dynamicObjectResolverRegistry->get($this->getDynamicObjectResolver());
            $dataObject = $resolver->resolve($this->getForm(), $this->getDynamicObjectResolverClass(), $this->getFormRuntimeData(), $this->getLocale(), self::OBJECT_RESOLVER_CREATE);
            $resolvingObjectIdentifier = $this->getDynamicObjectResolverClass();
        } else {
            /** @var DataObject\Concrete $dataObject */
            $dataObject = $this->modelFactory->build(sprintf('\Pimcore\Model\DataObject\%s', ucfirst($this->getResolvingObjectClass())));
            $dataObject->setParent($this->getStorageFolder());
            $dataObject->setKey(uniqid(sprintf('form-%d-', $formData->getFormDefinition()->getId()), true));
            $dataObject->setPublished(true);
            $resolvingObjectIdentifier = $this->getResolvingObjectClass();
        }

        if (!$dataObject instanceof DataObject\Concrete) {
            throw new \Exception(sprintf(
                'Resolving new object with identifier "%s" not found. %s',
                $resolvingObjectIdentifier,
                $this->getDynamicObjectResolver() === null ? '' : sprintf('Involved Resolver: "%s"', $this->getDynamicObjectResolver())
            ));
        }

        return $dataObject;
    }

    /**
     * @throws \Exception
     */
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
