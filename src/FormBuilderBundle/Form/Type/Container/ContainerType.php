<?php

namespace FormBuilderBundle\Form\Type\Container;

use FormBuilderBundle\Form\Type\ContainerCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'                => ContainerCollectionType::class,
            'required'                  => true,
            'formbuilder_configuration' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['formbuilder_configuration']['template']) && !empty($options['formbuilder_configuration']['template'])) {
            $view->vars['attr']['data-template'] = $options['formbuilder_configuration']['template'];
        }

        // prevent rendering required class on container root layer
        $view->vars['required'] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_builder_container';
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}