<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowResolverInterface
{
    public function resolve(SubmissionEvent $submissionEvent): ?OutputWorkflowInterface;
}
