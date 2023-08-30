<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker\Validation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidationCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label'           => false,
            'allow_add'       => true,
            'allow_delete'    => true,
            'by_reference'    => false,
            'entry_type'      => ValidationType::class,
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
