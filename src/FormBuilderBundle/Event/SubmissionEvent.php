<?php

namespace FormBuilderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends Event
{
    private Request $request;
    private FormInterface $form;
    private ?array $formRuntimeData;
    private ?string $redirectUri = null;
    private bool $outputWorkflowFinisherDisabled = false;

    public function __construct(Request $request, ?array $formRuntimeData, FormInterface $form)
    {
        $this->request = $request;
        $this->formRuntimeData = $formRuntimeData;
        $this->form = $form;
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

    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
