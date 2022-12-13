<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChannelActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('channelName', TextType::class);
    }
}
