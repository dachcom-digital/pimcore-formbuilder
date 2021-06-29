<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;

interface FormDefinitionFactoryInterface
{
    public function createFormDefinition(): FormDefinitionInterface;

    public function createFormFieldDefinition(): FormFieldDefinitionInterface;

    public function createFormFieldContainerDefinition(): FormFieldContainerDefinitionInterface;
}
