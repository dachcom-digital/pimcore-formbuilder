<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class PimcoreHrefType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', IntegerType::class);
        $builder->add('path', TextType::class);
        $builder->add('type', TextType::class);
        $builder->add('subtype', TextType::class);
    }
}
