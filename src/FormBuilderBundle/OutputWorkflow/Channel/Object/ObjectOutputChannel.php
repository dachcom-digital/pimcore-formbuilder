<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ObjectChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class ObjectOutputChannel implements ChannelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormType(): string
    {
        return ObjectChannelType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration)
    {

    }
}