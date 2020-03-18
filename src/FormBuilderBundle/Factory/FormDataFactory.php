<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\Data\FormData;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormDataFactory implements FormDataFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createFormData(FormDefinitionInterface $formDefinition)
    {
        $form = new FormData($formDefinition);

        return $form;
    }
}
