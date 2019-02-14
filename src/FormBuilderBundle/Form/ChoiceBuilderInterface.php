<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

interface ChoiceBuilderInterface
{
    /**
     * @param FormBuilderInterface $builder
     */
    public function setFormBuilder(FormBuilderInterface $builder);

    /**
     * @return mixed
     */
    public function getList();
}
