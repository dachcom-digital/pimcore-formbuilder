<?php

namespace FormBuilderBundle\Session;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

interface FlashBagManagerInterface
{
    public function has(string $type): bool;

    public function add(string $type, mixed $message);

    public function get(string $type, array $default = []): array;

    public function flashBagIsAvailable(): bool;

    public function getFlashBag(): ?FlashBagInterface;
}
