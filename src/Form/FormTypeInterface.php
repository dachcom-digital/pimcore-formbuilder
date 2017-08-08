<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormField;
use Symfony\Component\Form\FormBuilderInterface;

interface FormTypeInterface
{
    public function getType();

    public function getTitle();

    public function getTemplate();

    public function build(FormBuilderInterface $builder, FormField $field);

    /**
     * @param $options
     *
     * @return array
     */
    public function parseOptions(FormTypeOptionsMapper $options);

}
