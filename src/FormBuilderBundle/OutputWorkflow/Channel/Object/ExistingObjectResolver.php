<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use Pimcore\Model\DataObject;

class ExistingObjectResolver extends AbstractObjectResolver
{
    /**
     * @var array
     */
    protected $resolvingObject;

    /**
     * @param array $resolvingObject
     */
    public function setResolvingObject(array $resolvingObject)
    {
        $this->resolvingObject = $resolvingObject;
    }

    /**
     * @return array
     */
    public function getResolvingObject()
    {
        return $this->resolvingObject;
    }

    /**
     * {@inheritDoc}
     */
    public function getStorageObject()
    {
        $resolvingObjectInfo = $this->getResolvingObject();
        $resolvingObjectId = $resolvingObjectInfo['id'];
        $dataObject = DataObject::getById($resolvingObjectId);

        if (!$dataObject instanceof DataObject) {
            throw new \Exception(sprintf('Resolving Object with id "%s" not found.', $resolvingObjectId));
        }

        return $dataObject;
    }

    /**
     * {@inheritDoc}
     */
    public function fieldTypeAllowedToProcess($fieldType)
    {
        return $fieldType === 'container';
    }
}