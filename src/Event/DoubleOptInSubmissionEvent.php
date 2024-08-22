<?php

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class DoubleOptInSubmissionEvent extends Event
{
    public function __construct(
        private readonly Request $request,
        private readonly FormDefinitionInterface $formDefinition,
        private readonly FormInterface $form,
        private readonly bool $useFlashBag = true,
        private array $messages = []
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
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
