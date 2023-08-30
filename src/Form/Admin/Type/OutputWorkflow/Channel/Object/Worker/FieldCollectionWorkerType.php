<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\ObjectMappingElementCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker\Validation\ValidationCollectionType;

class FieldCollectionWorkerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fieldCollectionClassKey', TextType::class);
        $builder->add('fieldMapping', ObjectMappingElementCollectionType::class);
        $builder->add('validationData', ValidationCollectionType::class);
    }
}
