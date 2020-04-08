<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;

interface ObjectResolverFactoryInterface
{
    /**
     * @param array $objectMappingData
     *
     * @return NewObjectResolver
     */
    public function createForNewObject(array $objectMappingData);

    /**
     * @param array $objectMappingData
     *
     * @return ExistingObjectResolver
     */
    public function createForExistingObject(array $objectMappingData);
}
