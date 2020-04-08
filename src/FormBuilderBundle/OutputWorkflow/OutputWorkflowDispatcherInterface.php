<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowDispatcherInterface
{
    /**
     * @param OutputWorkflowInterface $outputWorkflow
     * @param SubmissionEvent         $submissionEvent
     *
     * @throws \Exception
     * @throws GuardException
     */
    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent);
}
