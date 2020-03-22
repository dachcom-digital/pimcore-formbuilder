<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

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
     * {@inheritDoc}
     */
    public function getStorageObject()
    {
        $pathName = sprintf('\Pimcore\Model\DataObject\%s', $this->getResolvingObjectClass());

        return new $pathName();
    }

    /**
     * {@inheritDoc}
     */
    public function fieldTypeAllowedToProcess($fieldType)
    {
        return true;
    }
}