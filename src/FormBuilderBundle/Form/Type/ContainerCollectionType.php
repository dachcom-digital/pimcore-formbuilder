<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['fields'] as $field) {
            $builder->add($field['name'], $field['type'], $field['options']);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['is_form_builder_container_block'] = true;
        $view->vars['add_block_counter'] = $options['add_block_counter'];
        // prevent rendering required class on container collection root layer
        $view->vars['required'] = false;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'fields'             => [],
            'container_type'     => null,
            'add_block_counter'  => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_container_collection';
    }
}
