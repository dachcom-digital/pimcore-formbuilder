<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;

interface SuccessManagementWorkerInterface
{
    public function process(SubmissionEvent $submissionEvent, array $successManagementConfiguration): void;
}
