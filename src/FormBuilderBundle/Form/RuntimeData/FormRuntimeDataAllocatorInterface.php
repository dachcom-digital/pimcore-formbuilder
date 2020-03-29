<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormRuntimeDataAllocatorInterface
{
    /**
     * @param FormDefinitionInterface $formDefinition
     * @param array                   $systemRuntimeData
     *
     * @return RuntimeDataCollector
     * @throws \Exception
     */
    public function allocate(FormDefinitionInterface $formDefinition, array $systemRuntimeData);
}
