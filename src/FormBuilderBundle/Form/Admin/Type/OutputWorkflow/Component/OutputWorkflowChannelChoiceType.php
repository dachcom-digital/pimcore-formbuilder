<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component;

use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputWorkflowChannelChoiceType extends AbstractType
{
    /**
     * @var array
     */
    private $channelRegistry;

    /**
     * @param OutputWorkflowChannelRegistry $channelRegistry
     */
    public function __construct(OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->channelRegistry = $channelRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->channelRegistry->getAllIdentifier(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
