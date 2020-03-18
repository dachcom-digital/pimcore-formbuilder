<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowResolverInterface
{
    /**
     * @param SubmissionEvent $submissionEvent
     *
     * @return OutputWorkflowInterface|null
     */
    public function resolve(SubmissionEvent $submissionEvent);
}