<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\EmailChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class EmailOutputChannel implements ChannelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType(): string
    {
        return EmailChannelType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizedConfiguration(): bool
    {
        return true;
    }
}