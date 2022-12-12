<?php

namespace FormBuilderBundle\Model;

class FunnelActionElement
{
    protected string $path;
    protected mixed $subject = null;
    protected bool $isDisabled = false;

    protected FunnelActionDefinition $funnelActionDefinition;
    protected array $coreConfiguration;

    public function __construct(
        FunnelActionDefinition $funnelActionDefinition,
        array $coreConfiguration
    ) {
        $this->funnelActionDefinition = $funnelActionDefinition;
        $this->coreConfiguration = $coreConfiguration;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSubject(): mixed
    {
        return $this->subject;
    }

    public function setSubject(mixed $subject): void
    {
        $this->subject = $subject;
    }

    public function isChannelAware(): bool
    {
        return $this->subject instanceof OutputWorkflowChannelInterface;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->isDisabled = $disabled;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled === true;
    }

    public function ignoreInvalidSubmission(): bool
    {
        if (!array_key_exists('ignoreInvalidFormSubmission', $this->coreConfiguration)) {
            return false;
        }

        return $this->coreConfiguration['ignoreInvalidFormSubmission'] === true;
    }

    public function getFunnelActionDefinition(): FunnelActionDefinition
    {
        return $this->funnelActionDefinition;
    }
}
