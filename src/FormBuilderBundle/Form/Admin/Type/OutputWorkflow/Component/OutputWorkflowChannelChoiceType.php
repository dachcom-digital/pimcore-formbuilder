<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component;

use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowChannelChoiceType extends AbstractType
{
    protected OutputWorkflowChannelRegistry $channelRegistry;

    public function __construct(OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->channelRegistry = $channelRegistry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->channelRegistry->getAllIdentifier(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
