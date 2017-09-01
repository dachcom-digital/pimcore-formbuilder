<?php

namespace FormBuilderBundle\Event;

use Pimcore\Mail;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class MailEvent extends Event
{
    /**
     * @var Request
     */
    private $form;

    /**
     * @var Mail
     */
    private $email;

    /**
     * MailEvent constructor.
     *
     * @param FormInterface $form
     * @param Mail          $email
     */
    public function __construct(FormInterface $form, Mail $email)
    {
        $this->form = $form;
        $this->email = $email;
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

}