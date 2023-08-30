<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\FormStorageData;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionElementStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class FunnelWorkerData
{
    protected ?FunnelActionElementStack $funnelActionElementStack = null;

    public function __construct(
        protected FunnelData $funnelData,
        protected SubmissionEvent $submissionEvent,
        protected OutputWorkflowInterface $outputWorkflow,
        protected OutputWorkflowChannelInterface $channel,
        protected ChannelInterface $channelProcessor
    ) {
    }

    public function getSubmissionEvent(): SubmissionEvent
    {
        return $this->submissionEvent;
    }

    public function getFunnelData(): FunnelData
    {
        return $this->funnelData;
    }

    public function getRequest(): Request
    {
        return $this->funnelData->getRequest();
    }

    public function getStorageToken(): string
    {
        return $this->funnelData->getStorageToken();
    }

    public function getFormStorageData(): FormStorageData
    {
        return $this->funnelData->getFormStorageData();
    }

    public function getOutputWorkflow(): OutputWorkflowInterface
    {
        return $this->outputWorkflow;
    }

    public function getChannel(): OutputWorkflowChannelInterface
    {
        return $this->channel;
    }

    public function getChannelProcessor(): ChannelInterface
    {
        return $this->channelProcessor;
    }

    public function setFunnelActionElementStack(FunnelActionElementStack $funnelActionElementStack): void
    {
        $this->funnelActionElementStack = $funnelActionElementStack;
    }

    public function getFunnelActionElementStack(): FunnelActionElementStack
    {
        return $this->funnelActionElementStack;
    }
}
