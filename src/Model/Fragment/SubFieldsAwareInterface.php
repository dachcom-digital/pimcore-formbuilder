<?php

namespace FormBuilderBundle\Model\Fragment;

use FormBuilderBundle\Model\FormFieldDefinitionInterface;

interface SubFieldsAwareInterface
{
    /**
     * @param array<int, FormFieldDefinitionInterface> $fields
     */
    public function setFields(array $fields): void;

    /**
     * @return array<int, FormFieldDefinitionInterface>
     */
    public function getFields(): array;
}
