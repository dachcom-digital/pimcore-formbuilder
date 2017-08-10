<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AbstractType implements TypeInterface
{
    /**
     * @var null
     */
    protected $type = NULL;

    /**
     * @var null
     */
    protected $template = NULL;

    /**
     * Returns type.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns template.
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param FormFieldInterface   $field
     */
    public function build(FormBuilderInterface $builder, FormFieldInterface $field)
    {
        $builder->add('field', TextType::class, $this->parseOptions($field->getOptions()));
    }

    /**
     * @param FormTypeOptionsMapper $options
     *
     * @return array
     */
    public function parseOptions(FormTypeOptionsMapper $options)
    {
        return [];
    }
}