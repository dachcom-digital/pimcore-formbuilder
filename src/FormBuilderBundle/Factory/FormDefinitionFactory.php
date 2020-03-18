<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormDefinition;
use FormBuilderBundle\Model\FormFieldContainerDefinition;
use FormBuilderBundle\Model\FormFieldDefinition;
use FormBuilderBundle\Model\FormFieldDynamicDefinition;

class FormDefinitionFactory implements FormDefinitionFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createFormDefinition()
    {
        $form = new FormDefinition();

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormFieldDefinition()
    {
        $formFieldEntity = new FormFieldDefinition();

        return $formFieldEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormFieldContainerDefinition()
    {
        $formFieldContainerEntity = new FormFieldContainerDefinition();

        return $formFieldContainerEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormFieldDynamicDefinition()
    {
        $formFieldDynamicEntity = new FormFieldDynamicDefinition();

        return $formFieldDynamicEntity;
    }
}
