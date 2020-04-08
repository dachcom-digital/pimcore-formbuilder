<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;
use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectResolverFactory implements ObjectResolverFactoryInterface
{
    /**
     * @var DynamicObjectResolverRegistry
     */
    protected $dynamicObjectResolverRegistry;

    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param DynamicObjectResolverRegistry    $dynamicObjectResolverRegistry
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        DynamicObjectResolverRegistry $dynamicObjectResolverRegistry,
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->dynamicObjectResolverRegistry = $dynamicObjectResolverRegistry;
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     *{@inheritdoc}
     */
    public function createForNewObject(array $objectMappingData)
    {
        return new NewObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $objectMappingData);
    }

    /**
     *{@inheritdoc}
     */
    public function createForExistingObject(array $objectMappingData)
    {
        $object = new ExistingObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $objectMappingData);
        $object->setDynamicObjectResolverRegistry($this->dynamicObjectResolverRegistry);

        return $object;
    }
}
