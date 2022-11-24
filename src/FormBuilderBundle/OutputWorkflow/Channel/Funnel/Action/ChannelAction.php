<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type\ChannelActionType;

class ChannelAction implements FunnelActionInterface
{
    public function getName(): string
    {
        return 'Go To Channel';
    }

    public function getFormType(): string
    {
        return ChannelActionType::class;
    }

}
