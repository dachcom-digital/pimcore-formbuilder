<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Pimcore\Model\DataObject;

class ExistingObjectResolver extends AbstractObjectResolver
{
    protected DynamicObjectResolverRegistry $dynamicObjectResolverRegistry;
    protected array $resolvingObject;
    protected ?string $dynamicObjectResolver;

    public function setDynamicObjectResolverRegistry(DynamicObjectResolverRegistry $dynamicObjectResolverRegistry): void
    {
        $this->dynamicObjectResolverRegistry = $dynamicObjectResolverRegistry;
    }

    public function setResolvingObject(array $resolvingObject): void
    {
        $this->resolvingObject = $resolvingObject;
    }

    public function getResolvingObject(): array
    {
        return $this->resolvingObject;
    }

    public function setDynamicObjectResolver(?string $dynamicObjectResolver): void
    {
        $this->dynamicObjectResolver = $dynamicObjectResolver;
    }

    public function getDynamicObjectResolver(): ?string
    {
        return $this->dynamicObjectResolver;
    }

    public function getStorageObject(): DataObject\Concrete
    {
        $resolvingObjectInfo = $this->getResolvingObject();
        $resolvingObjectId = $resolvingObjectInfo['id'];

        $dataObject = DataObject::getById($resolvingObjectId);

        if ($this->getDynamicObjectResolver() !== null) {
            $resolver = $this->dynamicObjectResolverRegistry->get($this->getDynamicObjectResolver());
            $dataObject = $resolver->resolve($this->getForm(), $dataObject, $this->getFormRuntimeData(), $this->getLocale());
        }

        if (!$dataObject instanceof DataObject\Concrete) {
            throw new \Exception(sprintf(
                'Resolving Object with id "%s" not found. %s',
                $resolvingObjectId,
                $this->getDynamicObjectResolver() === null ? '' : sprintf('Involved Resolver: "%s"', $this->getDynamicObjectResolver())
            ));
        }

        return $dataObject;
    }

    public function fieldTypeAllowedToProcess($fieldType): bool
    {
        return $fieldType === 'container';
    }
}
