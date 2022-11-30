<?php

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Form\RuntimeData\FunnelFormRuntimeData;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends Event
{
    private Request $request;
    private FormInterface $form;
    protected ?FunnelFormRuntimeData $funnelFormRuntimeData = null;

    private ?array $formRuntimeData;
    private ?string $redirectUri = null;
    private bool $outputWorkflowFinisherDisabled = false;

    public function __construct(Request $request, ?array $formRuntimeData, FormInterface $form, ?FunnelFormRuntimeData $funnelFormRuntimeData = null)
    {
        $this->request = $request;
        $this->formRuntimeData = $formRuntimeData;
        $this->form = $form;
        $this->funnelFormRuntimeData = $funnelFormRuntimeData;
    }

    public function disableOutputWorkflowFinisher(): void
    {
        $this->outputWorkflowFinisherDisabled = true;
    }

    public function outputWorkflowFinisherIsDisabled(): bool
    {
        return $this->outputWorkflowFinisherDisabled === true;
    }

    public function setRedirectUri(?string $uri): void
    {
        $this->redirectUri = $uri;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function hasRedirectUri(): bool
    {
        return !is_null($this->redirectUri);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFormRuntimeData(): ?array
    {
        return $this->formRuntimeData;
    }

    public function getFunnelFormRuntimeData(): ?FunnelFormRuntimeData
    {
        return $this->funnelFormRuntimeData;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

}
