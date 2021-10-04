<?php

namespace FormBuilderBundle\Event;

use Pimcore\Mail;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

/**
 * @deprecated since version 3.3; use \FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent instead.
 */
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
     * @param FormInterface $form
     * @param Mail          $email
     * @param array         $userOptions
     * @param bool          $isCopy
     */
    public function __construct(FormInterface $form, Mail $email, array $userOptions, $isCopy)
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
