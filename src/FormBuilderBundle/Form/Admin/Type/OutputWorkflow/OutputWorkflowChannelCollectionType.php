<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowChannelCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
            'entry_type'   => OutputWorkflowChannelType::class
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
