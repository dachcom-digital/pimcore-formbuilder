<?php

namespace FormBuilderBundle\Factory;

use Pimcore\Model\FactoryInterface;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;
use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ObjectResolverFactory implements ObjectResolverFactoryInterface
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected DynamicObjectResolverRegistry $dynamicObjectResolverRegistry,
        protected FormValuesOutputApplierInterface $formValuesOutputApplier,
        protected EventDispatcherInterface $eventDispatcher,
        protected FactoryInterface $modelFactory
    ) {
    }

    public function createForNewObject(array $objectMappingData): NewObjectResolver
    {
        return new NewObjectResolver(
            $this->translator,
            $this->formValuesOutputApplier,
            $this->eventDispatcher,
            $this->modelFactory,
            $this->dynamicObjectResolverRegistry,
            $objectMappingData
        );
    }

    public function createForExistingObject(array $objectMappingData): ExistingObjectResolver
    {
        return new ExistingObjectResolver(
            $this->translator,
            $this->formValuesOutputApplier,
            $this->eventDispatcher,
            $this->modelFactory,
            $this->dynamicObjectResolverRegistry,
            $objectMappingData
        );
    }
}
