<?php

namespace FormBuilderBundle\Event;

use Pimcore\Mail;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

class MailEvent extends Event
{
    private FormInterface $form;
    private Mail $email;
    private array $userOptions;
    private bool $isCopy;

    public function __construct(FormInterface $form, Mail $email, array $userOptions, bool $isCopy)
    {
        $this->form = $form;
        $this->email = $email;
        $this->isCopy = $isCopy;

        // dispatch legacy event
        if (isset($userOptions['email'])) {
            unset($userOptions['email']);
        }

        $this->userOptions = $userOptions;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function setEmail(Mail $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): Mail
    {
        return $this->email;
    }

    public function getUserOptions(): array
    {
        return $this->userOptions;
    }

    public function isCopy(): bool
    {
        return $this->isCopy;
    }
}
