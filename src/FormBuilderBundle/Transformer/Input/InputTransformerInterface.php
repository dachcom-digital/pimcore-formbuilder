<?php

namespace FormBuilderBundle\Transformer\Input;

use FormBuilderBundle\Model\FieldDefinitionInterface;

interface InputTransformerInterface
{
    public function getValueReverse(FieldDefinitionInterface $fieldDefinition,  mixed $formValue): mixed;
}
