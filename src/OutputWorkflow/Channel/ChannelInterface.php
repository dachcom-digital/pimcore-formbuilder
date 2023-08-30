<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;

interface ChannelInterface
{
    public function getFormType(): string;

    public function isLocalizedConfiguration(): bool;

    public function getUsedFormFieldNames(array $channelConfiguration): array;

    /**
     * @throws \Exception
     * @throws GuardException
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void;
}
