<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use Symfony\Component\HttpFoundation\Response;

interface ChannelResponseAwareInterface
{
    /**
     * @throws \Exception
     * @throws GuardException
     */
    public function dispatchResponseOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): ?Response;
}
