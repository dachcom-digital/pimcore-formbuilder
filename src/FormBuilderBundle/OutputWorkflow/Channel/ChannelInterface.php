<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

interface ChannelInterface
{
    /**
     * @return string
     */
    public function getFormType(): string;

    /**
     * @return bool
     */
    public function isLocalizedConfiguration();
}