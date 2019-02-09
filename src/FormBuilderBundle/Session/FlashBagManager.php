<?php

namespace FormBuilderBundle\Session;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FlashBagManager implements FlashBagManagerInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * MailListener constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function has($type)
    {
        if (!$this->flashBagIsAvailable()) {
            return false;
        }

        return $this->getFlashBag()->has($type);
    }

    /**
     * @inheritdoc
     */
    public function add($type, $message)
    {
        if (!$this->flashBagIsAvailable()) {
            return;
        }

        $this->getFlashBag()->add($type, $message);
    }

    /**
     * @inheritdoc
     */
    public function get($type, array $default = [])
    {
        if (!$this->flashBagIsAvailable()) {
            return [];
        }

        return $this->getFlashBag()->get($type, $default);
    }

    /**
     * @inheritdoc
     */
    public function flashBagIsAvailable()
    {
        return $this->session instanceof Session;
    }

    /**
     * @inheritdoc
     */
    public function getFlashBag()
    {
        if (!$this->session instanceof Session) {
            return null;
        }

        return $this->session->getFlashBag();
    }
}
