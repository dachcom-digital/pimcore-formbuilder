<?php

namespace FormBuilderBundle\Model;

interface FormFieldDynamicDefinitionInterface extends FieldDefinitionInterface
{
    public function getOptions(): array;

    public function getOptional(): array;
}
