<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

interface ChoiceBuilderInterface
{
    public function setFormBuilder(FormBuilderInterface $builder);

    public function getList();
}