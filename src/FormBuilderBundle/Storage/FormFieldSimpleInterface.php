<?php

namespace FormBuilderBundle\Storage;

interface FormFieldSimpleInterface
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

    /**
     * @return bool
     */
    public function isUpdated();
}
