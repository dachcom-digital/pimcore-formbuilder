<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;

interface SuccessManagementWorkerInterface
{
    /**
     * @throws \Exception
     */
    public function process(SubmissionEvent $submissionEvent, array $successManagementConfiguration): void;
}
