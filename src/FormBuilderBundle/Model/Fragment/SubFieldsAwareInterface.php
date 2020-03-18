<?php

namespace FormBuilderBundle\Model\Fragment;

use FormBuilderBundle\Model\FormFieldDefinitionInterface;

interface SubFieldsAwareInterface
{
    /**
     * @param FormFieldDefinitionInterface[] $fields
     */
    public function setFields(array $fields);

    /**
     * @return FormFieldDefinitionInterface[]
     */
    public function getFields();
}
