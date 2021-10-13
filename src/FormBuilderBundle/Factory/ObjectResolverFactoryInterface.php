<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;

interface ObjectResolverFactoryInterface
{
    public function createForNewObject(array $objectMappingData): NewObjectResolver;

    public function createForExistingObject(array $objectMappingData): ExistingObjectResolver;
}
