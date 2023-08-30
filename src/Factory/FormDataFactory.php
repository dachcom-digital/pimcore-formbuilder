<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\Data\FormData;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

class FormDataFactory implements FormDataFactoryInterface
{
    public function createFormData(FormDefinitionInterface $formDefinition, array $data = []): FormDataInterface
    {
        return new FormData($formDefinition, $data);
    }
}
