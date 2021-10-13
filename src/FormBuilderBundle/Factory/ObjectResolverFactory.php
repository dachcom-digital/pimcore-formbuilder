<?php

namespace FormBuilderBundle\Factory;

use Pimcore\Model\FactoryInterface;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;
use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ObjectResolverFactory implements ObjectResolverFactoryInterface
{
    protected DynamicObjectResolverRegistry $dynamicObjectResolverRegistry;
    protected FormValuesOutputApplierInterface $formValuesOutputApplier;
    protected EventDispatcherInterface $eventDispatcher;
    protected FactoryInterface $modelFactory;

    public function __construct(
        DynamicObjectResolverRegistry $dynamicObjectResolverRegistry,
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        EventDispatcherInterface $eventDispatcher,
        FactoryInterface $modelFactory
    ) {
        $this->dynamicObjectResolverRegistry = $dynamicObjectResolverRegistry;
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->eventDispatcher = $eventDispatcher;
        $this->modelFactory = $modelFactory;
    }

    public function createForNewObject(array $objectMappingData): NewObjectResolver
    {
        return new NewObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $this->modelFactory, $objectMappingData);
    }

    public function createForExistingObject(array $objectMappingData): ExistingObjectResolver
    {
        $object = new ExistingObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $this->modelFactory, $objectMappingData);
        $object->setDynamicObjectResolverRegistry($this->dynamicObjectResolverRegistry);

        return $object;
    }
}
