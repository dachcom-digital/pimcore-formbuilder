<?php

namespace FormBuilderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseSubmissionEvent extends Event
{
    protected readonly Request $request;
    protected FormInterface $form;
    protected bool $useFlashBag;
    protected array $messages;

    protected ?string $locale = null;

    public function __construct(
        Request $request,
        FormInterface $form,
        bool $useFlashBag = true,
        array $messages = []
    ) {
        $this->request = $request;
        $this->form = $form;
        $this->useFlashBag = $useFlashBag;
        $this->messages = $messages;
    }

    public function getRequest(): Request
    {
        return $this->request;
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

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }
}
