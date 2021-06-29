<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormRuntimeDataAllocatorInterface
{
    public function allocate(FormDefinitionInterface $formDefinition, array $systemRuntimeData): RuntimeDataCollector;
}
