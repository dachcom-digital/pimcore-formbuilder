<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\Worker\Validation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ValidationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', TextType::class);
        $builder->add('enabled', CheckboxType::class);
        $builder->add('field', TextType::class);
        $builder->add('message', TextType::class);
    }
}
