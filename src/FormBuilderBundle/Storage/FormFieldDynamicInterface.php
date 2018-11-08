<?php

namespace FormBuilderBundle\Storage;

interface FormFieldDynamicInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return array
     */
    public function getOptional();

    /**
     * @return int
     */
    public function getOrder();

    /**
     * @return bool
     */
    public function isUpdated();
}
