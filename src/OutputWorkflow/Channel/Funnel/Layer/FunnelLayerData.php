<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\HttpFoundation\Request;

class FunnelLayerData
{
    protected string $view;
    protected array $arguments = [];

    public function __construct(
        protected Request $request,
        protected SubmissionEvent $submissionEvent,
        protected array $funnelLayerConfiguration
    ) {
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

    public function setFunnelLayerViewArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getFunnelLayerViewArguments(): array
    {
        return $this->arguments;
    }
}
