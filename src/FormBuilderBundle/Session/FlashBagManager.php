<?php

namespace FormBuilderBundle\Session;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FlashBagManager implements FlashBagManagerInterface
{
    protected SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function has(string $type): bool
    {
        if (!$this->flashBagIsAvailable()) {
            return false;
        }

        return $this->getFlashBag()->has($type);
    }

    public function add(string $type, mixed $message): void
    {
        if (!$this->flashBagIsAvailable()) {
            return;
        }

        $this->getFlashBag()->add($type, $message);
    }

    public function get(string $type, array $default = []): array
    {
        if (!$this->flashBagIsAvailable()) {
            return [];
        }

        return $this->getFlashBag()->get($type, $default);
    }

    public function flashBagIsAvailable(): bool
    {
        return $this->session instanceof Session;
    }

    public function getFlashBag(): ?FlashBagInterface
    {
        if (!$this->session instanceof Session) {
            return null;
        }

        return $this->session->getFlashBag();
    }
}
