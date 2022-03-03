<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Api;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FieldConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('apiMapping', CollectionType::class, ['allow_add' => true, 'entry_type' => TextType::class]);
        $builder->add('fieldTransformer', TextType::class);
    }
}
