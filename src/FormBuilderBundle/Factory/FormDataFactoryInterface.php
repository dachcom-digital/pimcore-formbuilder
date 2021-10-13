<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDataFactoryInterface
{
    public function createFormData(FormDefinitionInterface $formDefinition): FormDataInterface;
}
