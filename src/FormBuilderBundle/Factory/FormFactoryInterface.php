<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\FormInterface;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;

interface FormFactoryInterface
{
    /**
     * @return FormInterface
     */
    public function createForm();

    /**
     * @return FormFieldInterface
     */
    public function createFormField();

    /**
     * @return FormFieldContainerInterface
     */
    public function createFormFieldContainer();
}
