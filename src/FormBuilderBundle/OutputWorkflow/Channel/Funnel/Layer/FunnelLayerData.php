<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\HttpFoundation\Request;

class FunnelLayerData
{
    public const RENDER_TYPE_INCLUDE = 'include';
    public const RENDER_TYPE_PRERENDER = 'prerender';

    protected string $view;
    protected string $renderType = self::RENDER_TYPE_INCLUDE;
    protected array $arguments = [];
    protected Request $request;
    protected SubmissionEvent $submissionEvent;
    protected array $funnelLayerConfiguration;

    public function __construct(
        Request $request,
        SubmissionEvent $submissionEvent,
        array $funnelLayerConfiguration
    ) {
        $this->request = $request;
        $this->submissionEvent = $submissionEvent;
        $this->funnelLayerConfiguration = $funnelLayerConfiguration;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRootFormSubmissionEvent(): SubmissionEvent
    {
        return $this->submissionEvent;
    }

    public function getFunnelLayerConfiguration(): array
    {
        return $this->funnelLayerConfiguration;
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
