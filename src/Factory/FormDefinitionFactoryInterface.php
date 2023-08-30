<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinition;

interface FormDefinitionFactoryInterface
{
    public function createFormDefinition(): FormDefinitionInterface;

    public function createFormFieldDefinition(): FormFieldDefinitionInterface;

    public function createFormFieldContainerDefinition(): FormFieldContainerDefinitionInterface;

    public function createFormFieldDynamicDefinition(string $name, string $type, array $options, array $optional = []): FormFieldDynamicDefinition;
}
