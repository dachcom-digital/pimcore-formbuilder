<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;

interface SuccessManagementWorkerInterface
{
    /**
     * @param SubmissionEvent $submissionEvent
     * @param array           $successManagementConfiguration
     *
     * @throws \Exception
     */
    public function process(SubmissionEvent $submissionEvent, array $successManagementConfiguration);
}