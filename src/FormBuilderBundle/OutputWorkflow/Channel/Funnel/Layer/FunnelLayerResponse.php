<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\OutputWorkflow\FunnelWorkerData;

class FunnelLayerResponse
{
    public const RENDER_TYPE_INCLUDE = 'include';
    public const RENDER_TYPE_PRERENDER = 'prerender';

    protected FunnelWorkerData $funnelWorkerData;

    protected string $view;
    protected string $renderType = self::RENDER_TYPE_INCLUDE;
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

    public function setRenderType(string $renderType): void
    {
        $this->renderType = $renderType;
    }

    public function getRenderType(): string
    {
        return $this->renderType;
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
