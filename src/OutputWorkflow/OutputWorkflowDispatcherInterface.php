<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardStackedException;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface OutputWorkflowDispatcherInterface
{
    /**
     * @throws \Exception
     * @throws GuardException
     * @throws GuardStackedException
     */
    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent);

    public function dispatchOutputWorkflowFunnelProcessing(OutputWorkflowInterface $outputWorkflow, Request $request): Response;
}
