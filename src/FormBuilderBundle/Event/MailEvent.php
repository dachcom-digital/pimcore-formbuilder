<?php

namespace FormBuilderBundle\Event;

use Pimcore\Mail;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

class MailEvent extends Event
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var Mail
     */
    private $email;

    /**
     * @var array
     */
    private $userOptions;

    /**
     * @var bool
     */
    private $isCopy;

    /**
     * MailEvent constructor.
     *
     * @param FormInterface $form
     * @param Mail          $email
     * @param array         $userOptions
     * @param bool          $isCopy
     */
    public function __construct(FormInterface $form, Mail $email, array $userOptions, $isCopy)
    {
        $this->form = $form;
        $this->email = $email;
        $this->userOptions = $userOptions;
        $this->isCopy = $isCopy;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Mail $email
     */
    public function setEmail(Mail $email)
    {
        $this->email = $email;
    }

    /**
     * @return Mail
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function getUserOptions()
    {
        return $this->userOptions;
    }

    /**
     * @return bool
     */
    public function isCopy()
    {
        return $this->isCopy;
    }
}