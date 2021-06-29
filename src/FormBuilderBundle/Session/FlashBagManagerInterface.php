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
     * Adds a flash message for type.
     */
    public function add(string $type, $message): void;

    /**
     * Gets and clears flash from the stack.
     */
    public function get(string $type, array $default = []): array;

    /**
     * @return bool
     */
    public function flashBagIsAvailable(): bool;

    /**
     * @return FlashBagInterface|null
     */
    public function getFlashBag(): ?FlashBagInterface;
}
