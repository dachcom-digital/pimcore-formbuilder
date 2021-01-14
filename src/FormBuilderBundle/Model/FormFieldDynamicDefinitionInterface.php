<?php

namespace FormBuilderBundle\Model;

interface FormFieldDynamicDefinitionInterface extends FieldDefinitionInterface
{
    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return array
     */
    public function getOptional();
}
