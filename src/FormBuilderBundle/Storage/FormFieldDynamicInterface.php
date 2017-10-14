<?php

namespace FormBuilderBundle\Storage;

interface FormFieldDynamicInterface
{
    public function getName();

    public function getType();

    public function getOptions();

    public function getOptional();

    public function getOrder();

    public function isUpdated();
}
