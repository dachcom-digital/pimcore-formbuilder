<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\RuntimeData\FunnelFormRuntimeData;
use FormBuilderBundle\Model\FormStorageData;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionElementStack;
use FormBuilderBundle\Storage\StorageProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class FunnelWorkerData
{
    protected Request $request;
    protected OutputWorkflowInterface $outputWorkflow;
    protected OutputWorkflowChannelInterface $channel;
    protected ChannelInterface $channelProcessor;
    protected SubmissionEvent $submissionEvent;
    protected FunnelFormRuntimeData $funnelFormRuntimeData;
    protected FormStorageData $formStorageData;
    protected StorageProviderInterface $storageProvider;
    protected ?FunnelActionElementStack $funnelActionElementStack = null;
    protected string $storageToken;

    public function __construct(
        Request $request,
        OutputWorkflowInterface $outputWorkflow,
        OutputWorkflowChannelInterface $channel,
        ChannelInterface $channelProcessor,
        SubmissionEvent $submissionEvent,
        FunnelFormRuntimeData $funnelFormRuntimeData,
        FormStorageData $formStorageData,
        StorageProviderInterface $storageProvider,
        string $storageToken,
    ) {
        $this->request = $request;
        $this->outputWorkflow = $outputWorkflow;
        $this->channel = $channel;
        $this->channelProcessor = $channelProcessor;
        $this->submissionEvent = $submissionEvent;
        $this->funnelFormRuntimeData = $funnelFormRuntimeData;
        $this->formStorageData = $formStorageData;
        $this->storageProvider = $storageProvider;
        $this->storageToken = $storageToken;
    }

    public function getRequest(): Request
    {
        return $this->request;
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

    public function getSubmissionEvent(): SubmissionEvent
    {
        return $this->submissionEvent;
    }

    public function getFunnelFormRuntimeData(): FunnelFormRuntimeData
    {
        return $this->funnelFormRuntimeData;
    }

    public function getFormStorageData(): FormStorageData
    {
        return $this->formStorageData;
    }

    public function getStorageProvider(): StorageProviderInterface
    {
        return $this->storageProvider;
    }

    public function setFunnelActionElementStack(FunnelActionElementStack $funnelActionElementStack): void
    {
        $this->funnelActionElementStack = $funnelActionElementStack;
    }

    public function getFunnelActionElementStack(): FunnelActionElementStack
    {
        return $this->funnelActionElementStack;
    }

    public function getStorageToken(): string
    {
        return $this->storageToken;
    }
}
