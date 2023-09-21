<?php

namespace FormBuilderBundle\Session;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

interface FlashBagManagerInterface
{
    /**
     * Has flash messages for a given type?
     */
    public function has(string $type): bool;

    /**
     * Adds a flash message for type
     */
    public function add(string $type, mixed $message);

    /**
     * Gets and clears flash from the stack
     */
    public function get(string $type, array $default = []): array;

    public function flashBagIsAvailable(): bool;

    public function getFlashBag(): ?FlashBagInterface;
}
