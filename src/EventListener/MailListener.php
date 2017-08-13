<?php

namespace FormBuilderBundle\EventListener;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Parser\MailParser;
use Pimcore\Model\Document\Email;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class MailListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var MailParser
     */
    protected $mailParser;

    /**
     * MailListener constructor.
     *
     * @param Session $session
     * @param MailParser $mailParser
     */
    public function __construct(Session $session, MailParser $mailParser)
    {
        $this->session = $session;
        $this->mailParser = $mailParser;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormBuilderEvents::FORM_SUBMIT_SUCCESS => ['onFormSubmit'],
        ];
    }

    /**
     * @param SubmissionEvent $event
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        $request = $event->getRequest();
        $form = $event->getForm();

        $formConfiguration = $event->getFormConfiguration();
        $emailConfiguration = $formConfiguration['email'];
        $sendCopy = $emailConfiguration['send_copy'];
        $flashBag = $this->session->getFlashBag();

        if (empty($emailConfiguration['mail_template_id'])) {
            $flashBag->add('error', 'no valid mail template given.');
            return;
        }

        try {

            $send = $this->sendForm($emailConfiguration['mail_template_id'], $form, $request->getLocale());

            if ($send === TRUE) {
                //send copy!
                if ($sendCopy === TRUE) {
                    try {
                        $send = $this->sendForm($emailConfiguration['copy_mail_template_id'], $form, $request->getLocale());
                        if ($send !== TRUE) {
                            $flashBag->add('error', 'copy mail not sent.');
                        }
                    } catch (\Exception $e) {
                        $flashBag->add('error', 'copy mail sent error: ' . $e->getMessage());
                    }
                }
            } else {
                $flashBag->add('error', 'mail not sent.');
            }

        } catch (\Exception $e) {
            $flashBag->add('error', 'error while sending mail: ' . $e->getMessage());
        }
    }

    /**
     * @param int   $mailTemplateId
     * @param FormInterface $form
     * @param $locale
     * @throws \Exception
     * @returns bool
     */
    private function sendForm($mailTemplateId = 0, FormInterface $form, $locale)
    {
        $mailTemplate = Email::getById($mailTemplateId);
        if (!$mailTemplate instanceof Email) {
            return FALSE;
        }

        $mail = $this->mailParser->create($mailTemplate, $form, $locale);
        $mail->send();

        return TRUE;
    }

}
