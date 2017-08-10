<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;

interface TypeInterface
{
    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getTemplate();

    /**
     * @param FormBuilderInterface $builder
     * @param FormFieldInterface   $field
     *
     */
    public function build(FormBuilderInterface $builder, FormFieldInterface $field);

    /**
     * @param $options
     *
     * @return array
     */
    public function parseOptions(FormTypeOptionsMapper $options);

}
