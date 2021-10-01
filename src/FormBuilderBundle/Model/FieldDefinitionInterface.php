<?php

namespace FormBuilderBundle\Model;

interface FieldDefinitionInterface
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
