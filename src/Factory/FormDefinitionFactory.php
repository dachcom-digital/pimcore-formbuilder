<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormFieldContainerDefinition;
use FormBuilderBundle\Model\FormFieldDefinition;
use FormBuilderBundle\Model\FormFieldDynamicDefinition;

class FormDefinitionFactory implements FormDefinitionFactoryInterface
{
    public function createFormDefinition(): FormDefinition
    {
        return new FormDefinition();
    }

    public function createFormFieldDefinition(): FormFieldDefinition
    {
        return new FormFieldDefinition();
    }

    public function createFormFieldContainerDefinition(): FormFieldContainerDefinition
    {
        return new FormFieldContainerDefinition();
    }

    public function createFormFieldDynamicDefinition(string $name, string $type, array $options, array $optional = []): FormFieldDynamicDefinition
    {
        return new FormFieldDynamicDefinition($name, $type, $options, $optional);
    }
}
