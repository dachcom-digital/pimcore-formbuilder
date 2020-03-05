<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\Form;
use FormBuilderBundle\Storage\FormFieldContainer;
use FormBuilderBundle\Storage\FormField;

class FormFactory implements FormFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createForm()
    {
        $form = new Form();

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormField()
    {
        $formFieldEntity = new FormField();

        return $formFieldEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormFieldContainer()
    {
        $formFieldContainerEntity = new FormFieldContainer();

        return $formFieldContainerEntity;
    }

}
