<?php

namespace FormBuilderBundle\Storage;

interface FormFieldDynamicInterface
{
    /**
     * @return string|false
     */
    public function getName();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getOptions();

    /**
     * @return string
     */
    public function getOptional();

    /**
     * @return int|mixed
     */
    public function getOrder();

    /**
     * @return bool
     */
    public function isUpdated();
}
