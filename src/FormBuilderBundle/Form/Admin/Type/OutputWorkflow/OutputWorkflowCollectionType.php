<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use FormBuilderBundle\Model\OutputWorkflow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label'           => false,
            'auto_initialize' => false,
            'allow_add'       => true,
            'allow_delete'    => true,
            'entry_type'      => OutputWorkflow::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
