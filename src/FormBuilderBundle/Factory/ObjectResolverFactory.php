<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ObjectResolverFactory implements ObjectResolverFactoryInterface
{
    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     *{@inheritdoc}
     */
    public function createForNewObject(array $storagePath, array $objectMappingData)
    {
        return new NewObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $storagePath, $objectMappingData);
    }

    /**
     *{@inheritdoc}
     */
    public function createForExistingObject(array $storagePath, array $objectMappingData)
    {
        return new ExistingObjectResolver($this->formValuesOutputApplier, $this->eventDispatcher, $storagePath, $objectMappingData);
    }
}
