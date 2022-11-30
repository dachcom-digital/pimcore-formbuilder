<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FunnelWorkerInterface
{
    /**
     * @throws \Exception
     */
    public function initiateFunnel(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void;

    /**
     * @throws \Exception
     */
    public function processFunnel(Request $request, string $funnelId, string $channelId, string $storageToken): Response;
}
