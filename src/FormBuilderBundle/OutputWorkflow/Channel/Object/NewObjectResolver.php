<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Form\Data\FormDataInterface;
use Pimcore\Model\DataObject\Concrete;

class NewObjectResolver extends AbstractObjectResolver
{
    /**
     * @var string
     */
    protected $resolvingObjectClass;

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
     * {@inheritdoc}
     */
    public function getStorageObject()
    {
        /** @var FormDataInterface $formData */
        $formData = $this->getForm()->getData();

        $storageFolder = $this->getStorageFolder();
        $pathName = sprintf('\Pimcore\Model\DataObject\%s', ucfirst($this->getResolvingObjectClass()));

        /** @var Concrete $object */
        $object = new $pathName();

        $object->setParent($storageFolder);
        $object->setKey(uniqid(sprintf('form-%d-', $formData->getFormDefinition()->getId())));
        $object->setPublished(true);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function fieldTypeAllowedToProcess($fieldType)
    {
        return true;
    }
}