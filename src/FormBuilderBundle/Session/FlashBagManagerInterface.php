<?php

namespace FormBuilderBundle\Session;

use Pimcore\Session\SessionConfiguratorInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface FlashBagManagerInterface
{
    /**
     * Has flash messages for a given type?
     *
     * @param string $type
     *
     * @return bool
     */
    public function has($type);

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param mixed  $message
     */
    public function add($type, $message);

    /**
     * Gets and clears flash from the stack.
     *
     * @param string $type
     * @param array  $default Default value if $type does not exist
     *
     * @return array
     */
    public function get($type, array $default = []);

    /**
     * @return bool
     */
    public function flashBagIsAvailable();

    /**
     * @return FlashBagInterface|null
     */
    public function getFlashBag();
}
