<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use Pimcore\Model\DataObject;

class ExistingObjectResolver extends AbstractObjectResolver
{
    protected array $resolvingObject;

    public function setResolvingObject(array $resolvingObject): void
    {
        $this->resolvingObject = $resolvingObject;
    }

    public function getResolvingObject(): array
    {
        return $this->resolvingObject;
    }

    public function getStorageObject(): DataObject\Concrete
    {
        if ($this->getDynamicObjectResolver() !== null) {
            $resolver = $this->dynamicObjectResolverRegistry->get($this->getDynamicObjectResolver());
            $dataObject = $resolver->resolve($this->getForm(), $this->getDynamicObjectResolverClass(), $this->getFormRuntimeData(), $this->getLocale(), self::OBJECT_RESOLVER_UPDATE);
            $resolvingObjectIdentifier = $this->getDynamicObjectResolverClass();
        } else {
            $resolvingObjectInfo = $this->getResolvingObject();
            $resolvingObjectIdentifier = $resolvingObjectInfo['id'];
            $dataObject = DataObject::getById($resolvingObjectIdentifier);
        }

        if (!$dataObject instanceof DataObject\Concrete) {
            throw new \Exception(sprintf(
                'Resolving existing object with identifier "%s" not found. %s',
                $resolvingObjectIdentifier,
                $this->getDynamicObjectResolver() === null ? '' : sprintf('Involved resolver: "%s"', $this->getDynamicObjectResolver())
            ));
        }

        return $dataObject;
    }

    public function fieldTypeAllowedToProcess($fieldType): bool
    {
        return $fieldType === 'container';
    }
}
