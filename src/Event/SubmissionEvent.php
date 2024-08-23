<?php

namespace FormBuilderBundle\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends BaseSubmissionEvent
{
    protected ?array $formRuntimeData;
    protected ?array $funnelRuntimeData;

    protected ?string $redirectUri = null;
    protected bool $outputWorkflowFinisherDisabled = false;

    public function __construct(
        Request $request,
        ?array $formRuntimeData,
        FormInterface $form,
        ?array $funnelRuntimeData = null,
        bool $useFlashBag = true,
        array $messages = []
    ) {
        parent::__construct(
            $request,
            $form,
            $useFlashBag,
            $messages
        );

        $this->formRuntimeData = $formRuntimeData;
        $this->funnelRuntimeData = $funnelRuntimeData;
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

    public function getFormRuntimeData(): ?array
    {
        return $this->formRuntimeData;
    }

    public function getFunnelRuntimeData(): ?array
    {
        return $this->funnelRuntimeData;
    }
}
