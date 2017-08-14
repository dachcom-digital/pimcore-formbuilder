<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConstraintsCollectionType extends CollectionType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('entry_type', ConstraintType::class);
        $resolver->setDefault('allow_add', true);
        $resolver->setDefault('allow_remove', true);
    }
}
