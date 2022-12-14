<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormValuesInputApplierInterface
{
    public function apply(array $form, FormDefinitionInterface $formDefinition): array;
}
