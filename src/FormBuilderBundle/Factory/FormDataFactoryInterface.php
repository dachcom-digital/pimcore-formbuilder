<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDataFactoryInterface
{
    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return FormDataInterface
     */
    public function createFormData(FormDefinitionInterface $formDefinition);
}
