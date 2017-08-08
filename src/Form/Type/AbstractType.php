<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Form\FormTypeInterface;
use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AbstractType implements FormTypeInterface
{
    protected $type = NULL;

    protected $title = NULL;

    protected $template = NULL;

    /**
     * Returns title.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns template.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param FormField            $field
     */
    public function build(FormBuilderInterface $builder, FormField $field) {
        $builder->add('field', TextType::class, $this->parseOptions($field->getOptions()));
    }

    /**
     * @param FormTypeOptionsMapper $options
     *
     * @return array
     */
    public function parseOptions(FormTypeOptionsMapper $options) {
        return [];
    }
}