<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

interface ChannelContextAwareInterface
{
    public function setChannelContext(ChannelContext $channelContext);

    public function getChannelContext(): ChannelContext;
}
