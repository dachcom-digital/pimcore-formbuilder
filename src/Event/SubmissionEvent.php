<?php

namespace FormBuilderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends Event
{
    private ?string $redirectUri = null;
    private bool $outputWorkflowFinisherDisabled = false;

    public function __construct(
        private readonly Request $request,
        private readonly ?array $formRuntimeData,
        private readonly FormInterface $form,
        private ?array $funnelRuntimeData = null,
        private bool $useFlashBag = true,
        private array $messages = []
    ) {
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

    public function getFunnelRuntimeData(): ?array
    {
        return $this->funnelRuntimeData;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function useFlashBag(): bool
    {
        return $this->useFlashBag;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMessagesOfType(string $type): array
    {
        return $this->messages[$type] ?? [];
    }

    public function hasMessagesOfType(string $type): bool
    {
        return array_key_exists($type, $this->messages);
    }

    public function addMessage(string $type, mixed $message): void
    {
        if (empty($message)) {
            return;
        }

        if (!array_key_exists($type, $this->messages)) {
            $this->messages[$type] = [];
        }

        $this->messages[$type][] = $message;
    }
}
