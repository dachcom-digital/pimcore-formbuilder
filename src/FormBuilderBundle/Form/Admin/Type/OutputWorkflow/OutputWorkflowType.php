<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use FormBuilderBundle\Model\OutputWorkflow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);
        $builder->add('successManagement', SuccessManagementType::class);
        $builder->add('channels', OutputWorkflowChannelCollectionType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => OutputWorkflow::class
        ]);
    }
}
