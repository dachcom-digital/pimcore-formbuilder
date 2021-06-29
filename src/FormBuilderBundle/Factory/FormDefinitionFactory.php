<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinition;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinition;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinition;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;

class FormDefinitionFactory implements FormDefinitionFactoryInterface
{
    public function createFormDefinition(): FormDefinitionInterface
    {
        return new FormDefinition();
    }

    public function createFormFieldDefinition(): FormFieldDefinitionInterface
    {
        return new FormFieldDefinition();
    }

    public function createFormFieldContainerDefinition(): FormFieldContainerDefinitionInterface
    {
        return new FormFieldContainerDefinition();
    }

    public function createFormFieldDynamicDefinition(string $name, string $type, array $options, array $optional = []): FormFieldDynamicDefinitionInterface
    {
        return new FormFieldDynamicDefinition($name, $type, $options, $optional);
    }
}
