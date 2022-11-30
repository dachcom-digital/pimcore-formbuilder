<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\OutputWorkflow\FunnelWorkerData;

class FunnelLayerResponse
{
    protected FunnelWorkerData $funnelWorkerData;

    protected string $view;
    protected array $arguments = [];

    public function __construct(FunnelWorkerData $funnelWorkerData)
    {
        $this->funnelWorkerData = $funnelWorkerData;
    }

    public function getFunnelWorkerData(): FunnelWorkerData
    {
        return $this->funnelWorkerData;
    }

    public function setFunnelLayerView(string $view): void
    {
        $this->view = $view;
    }

    public function getFunnelLayerView(): string
    {
        return $this->view;
    }

    public function setFunnelLayerViewArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getFunnelLayerViewArguments(): array
    {
        return $this->arguments;
    }
}
