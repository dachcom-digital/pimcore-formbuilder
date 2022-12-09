<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Model\FormStorageData;
use Symfony\Component\HttpFoundation\Request;

class FunnelData
{
    protected Request $request;
    protected FormStorageData $formStorageData;
    protected string $funnelId;
    protected string $channelId;
    protected string $storageToken;

    public function __construct(
        Request $request,
        FormStorageData $formStorageData,
        string $funnelId,
        string $channelId,
        string $storageToken
    ) {
        $this->request = $request;
        $this->formStorageData = $formStorageData;
        $this->funnelId = $funnelId;
        $this->channelId = $channelId;
        $this->storageToken = $storageToken;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFormStorageData(): FormStorageData
    {
        return $this->formStorageData;
    }

    public function getFunnelId(): string
    {
        return $this->funnelId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function getStorageToken(): string
    {
        return $this->storageToken;
    }
}
