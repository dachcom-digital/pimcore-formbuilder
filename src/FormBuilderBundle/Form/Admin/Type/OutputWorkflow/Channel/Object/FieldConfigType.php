<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldConfigType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['config_type'] === 'form_field') {
            $this->buildFieldConfig($builder);
        } elseif ($options['config_type'] === 'data_class_field') {
            $this->buildDataClassFieldConfig($builder);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function buildFieldConfig(FormBuilderInterface $builder)
    {
        $builder->add('name', TextType::class);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function buildDataClassFieldConfig(FormBuilderInterface $builder)
    {
        $builder->add('name', TextType::class);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'config_type'       => null,
            'field_config_type' => null,
        ]);
    }
}
