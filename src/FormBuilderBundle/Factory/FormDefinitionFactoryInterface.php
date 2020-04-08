<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldContainerDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;

interface FormDefinitionFactoryInterface
{
    /**
     * @return FormDefinitionInterface
     */
    public function createFormDefinition();

    /**
     * @return FormFieldDefinitionInterface
     */
    public function createFormFieldDefinition();

    /**
     * @return FormFieldContainerDefinitionInterface
     */
    public function createFormFieldContainerDefinition();
}
