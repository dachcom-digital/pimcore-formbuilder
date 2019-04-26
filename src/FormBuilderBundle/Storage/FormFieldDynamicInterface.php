<?php

namespace FormBuilderBundle\Storage;

interface FormFieldDynamicInterface extends FormFieldSimpleInterface
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
