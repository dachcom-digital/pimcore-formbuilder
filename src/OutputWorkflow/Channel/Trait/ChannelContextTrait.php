<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Trait;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelContext;

trait ChannelContextTrait
{
    protected ChannelContext $channelContext;

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function setChannelContext(ChannelContext $channelContext): void
    {
        $this->channelContext = $channelContext;
    }
}
