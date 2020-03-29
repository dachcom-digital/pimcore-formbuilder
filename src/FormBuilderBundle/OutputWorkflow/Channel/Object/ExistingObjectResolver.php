<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Pimcore\Model\DataObject;

class ExistingObjectResolver extends AbstractObjectResolver
{
    /**
     * @var DynamicObjectResolverRegistry
     */
    protected $dynamicObjectResolverRegistry;

    /**
     * @var array
     */
    protected $resolvingObject;

    /**
     * @var string|null
     */
    protected $dynamicObjectResolver;

    /**
     * @param DynamicObjectResolverRegistry $dynamicObjectResolverRegistry
     */
    public function setDynamicObjectResolverRegistry(DynamicObjectResolverRegistry $dynamicObjectResolverRegistry)
    {
        $this->dynamicObjectResolverRegistry = $dynamicObjectResolverRegistry;
    }

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
     * @param string|null $dynamicObjectResolver
     */
    public function setDynamicObjectResolver($dynamicObjectResolver)
    {
        $this->dynamicObjectResolver = $dynamicObjectResolver;
    }

    /**
     * @return string|null
     */
    public function getDynamicObjectResolver()
    {
        return $this->dynamicObjectResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageObject()
    {
        $resolvingObjectInfo = $this->getResolvingObject();
        $resolvingObjectId = $resolvingObjectInfo['id'];

        $resolver = null;
        $dataObject = DataObject::getById($resolvingObjectId);

        if ($this->getDynamicObjectResolver() !== null) {
            $resolver = $this->dynamicObjectResolverRegistry->get($this->getDynamicObjectResolver());
            $dataObject = $resolver->resolve($this->getForm(), $dataObject, $this->getFormRuntimeData(), $this->getLocale());
        }

        if (!$dataObject instanceof DataObject) {
            throw new \Exception(sprintf(
                'Resolving Object with id "%s" not found. %s',
                $resolvingObjectId,
                $this->getDynamicObjectResolver() === null ? '' : sprintf('Involved Resolver: "%s"', $this->getDynamicObjectResolver())
            ));
        }

        return $dataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function fieldTypeAllowedToProcess($fieldType)
    {
        return $fieldType === 'container';
    }
}
