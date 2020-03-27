<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;

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

    /**
     * @param array $channelConfiguration
     *
     * @return array
     */
    public function getUsedFormFieldNames(array $channelConfiguration);

    /**
     * @param SubmissionEvent $submissionEvent
     * @param string          $workflowName
     * @param array           $channelConfiguration
     *
     * @throws \Exception
     * @throws GuardException
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration);
}