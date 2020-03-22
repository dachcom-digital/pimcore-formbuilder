<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;

class ObjectResolverFactory implements ObjectResolverFactoryInterface
{
    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     */
    public function __construct(FormValuesOutputApplierInterface $formValuesOutputApplier)
    {
        $this->formValuesOutputApplier = $formValuesOutputApplier;
    }

    /**
     *{@inheritDoc}
     */
    public function createForNewObject(array $storagePath, array $objectMappingData)
    {
        return new NewObjectResolver($this->formValuesOutputApplier, $storagePath, $objectMappingData);
    }

    /**
     *{@inheritDoc}
     */
    public function createForExistingObject(array $storagePath, array $objectMappingData)
    {
        return new ExistingObjectResolver($this->formValuesOutputApplier, $storagePath, $objectMappingData);
    }
}
