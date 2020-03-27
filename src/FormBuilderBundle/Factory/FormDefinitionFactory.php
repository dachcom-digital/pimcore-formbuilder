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
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $optional
     *
     * @return FormFieldDynamicDefinition
     */
    public function createFormFieldDynamicDefinition(string $name, string $type, array $options, array $optional = [])
    {
        $formFieldDynamicEntity = new FormFieldDynamicDefinition($name, $type, $options, $optional);

        return $formFieldDynamicEntity;
    }
}
