<?php

namespace FormBuilderBundle\Model;

use FormBuilderBundle\Storage\FormFieldSimpleInterface;

interface FieldDefinitionInterface extends FormFieldSimpleInterface
{
    /**
     * @return int
     */
    public function getOrder();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getType();
}
