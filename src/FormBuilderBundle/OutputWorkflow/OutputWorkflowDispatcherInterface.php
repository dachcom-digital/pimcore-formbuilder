<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowDispatcherInterface
{
    /**
     * @param OutputWorkflowInterface $outputWorkflow
     * @param SubmissionEvent         $submissionEvent
     *
     * @throws \Exception
     */
    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent);
}