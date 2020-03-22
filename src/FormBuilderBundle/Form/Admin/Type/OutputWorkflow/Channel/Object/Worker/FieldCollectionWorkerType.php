<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\ObjectMappingElementCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FieldCollectionWorkerType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fieldCollectionClassKey', TextType::class);
        $builder->add('fieldMapping', ObjectMappingElementCollectionType::class);
    }
}
