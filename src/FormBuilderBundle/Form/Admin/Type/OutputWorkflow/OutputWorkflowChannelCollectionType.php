<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\FunnelActionsCollectionType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\OutputWorkflowChannelChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowChannelCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', OutputWorkflowChannelChoiceType::class, []);
        $builder->add('name', TextType::class, []);
        $builder->add('funnelActions', FunnelActionsCollectionType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'auto_initialize' => false,
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
