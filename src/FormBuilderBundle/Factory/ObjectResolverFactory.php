<?php

namespace FormBuilderBundle\Factory;

use Pimcore\Model\FactoryInterface;
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
     * @var FactoryInterface
     */
    protected $modelFactory;

    /**
     * @param DynamicObjectResolverRegistry    $dynamicObjectResolverRegistry
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param EventDispatcherInterface         $eventDispatcher
     * @param FactoryInterface                 $modelFactory
     */
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

    /**
     *{@inheritdoc}
     */
    public function createForNewObject(array $objectMappingData)
    {
        return new NewObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $this->modelFactory, $objectMappingData);
    }

    /**
     *{@inheritdoc}
     */
    public function createForExistingObject(array $objectMappingData)
    {
        $object = new ExistingObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $this->modelFactory, $objectMappingData);
        $object->setDynamicObjectResolverRegistry($this->dynamicObjectResolverRegistry);

        return $object;
    }
}
