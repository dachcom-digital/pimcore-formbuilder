<?php

namespace FormBuilderBundle\EventListener;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Mail\FormBuilderMail;
use Pimcore\Model\Document\Email;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

class MailListener implements EventSubscriberInterface
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var bool
     */
    private $isValid = FALSE;

    /**
     * MailListener constructor.
     *
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
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
        $formData = $event->getFormData();

        $formConfiguration = $event->getFormConfiguration();
        $emailConfiguration = $formConfiguration['email'];
        $sendCopy = $emailConfiguration['send_copy'];

        if (empty($emailConfiguration['mail_template_id'])) {
            $this->log('no valid mail template given.');
        }

        try {

            $send = $this->sendForm($emailConfiguration['mail_template_id'], ['data' => $formData]);

            if ($send === TRUE) {
                $this->isValid = TRUE;

                //send copy!
                if ($sendCopy === TRUE) {
                    try {
                        $send = $this->sendForm($emailConfiguration['copy_mail_template_id'], ['data' => $formData]);

                        if ($send !== TRUE) {
                            $this->log('copy mail not sent.');
                            $this->isValid = FALSE;
                        }
                    } catch (\Exception $e) {
                        $this->log('copy mail sent error: ' . $e->getMessage());
                        $this->isValid = FALSE;
                    }
                }
            } else {
                $this->log('mail not sent.');
            }

        } catch (\Exception $e) {
            $this->log('mail sent error: ' . $e->getMessage());
            $this->isValid = FALSE;
        }
    }

    /**
     * @param int   $mailTemplateId
     * @param array $attributes
     *
     * @throws \Exception
     * @returns bool
     */
    private function sendForm($mailTemplateId = 0, $attributes = [])
    {
        $mailTemplate = Email::getById($mailTemplateId);

        if (!$mailTemplate instanceof Email) {
            return FALSE;
        }

        $this->setMailRecipients($attributes['data'], $mailTemplate);
        $this->setMailSender($attributes['data'], $mailTemplate);

        $disableDefaultMailBody = (bool)$mailTemplate->getProperty('mail_disable_default_mail_body');
        $ignoreFieldData = (string)$mailTemplate->getProperty('mail_ignore_fields');

        $ignoreFields = array_map('trim', explode(',', $ignoreFieldData));

        $mail = new FormbuilderMail();
        $mail->setTemplateEngine($this->templating);

        $mail->setDocument($mailTemplate);
        $mail->setIgnoreFields($ignoreFields);
        $mail->parseSubject($mailTemplate->getSubject(), $attributes['data']);
        $mail->setMailPlaceholders($attributes['data'], $disableDefaultMailBody);

        $mail->send();

        return TRUE;
    }

    /**
     * @param array                         $data
     * @param \Pimcore\Model\Document\Email $mailTemplate
     */
    private function setMailRecipients($data = [], $mailTemplate)
    {
        $to = $mailTemplate->getTo();
        $parsedTo = $this->extractPlaceHolder($to, $data);

        $mailTemplate->setTo($parsedTo);
    }

    /**
     * @param array                         $data
     * @param \Pimcore\Model\Document\Email $mailTemplate
     */
    private function setMailSender($data = [], $mailTemplate)
    {
        $from = $mailTemplate->getFrom();
        $parsedFrom = $this->extractPlaceHolder($from, $data);

        $mailTemplate->setFrom($parsedFrom);
    }

    /**
     * Extract Placeholder Data from given String like %email% and compare it with given form data.
     *
     * @param $str
     * @param $data
     *
     * @return mixed|string
     */
    private function extractPlaceHolder($str, $data)
    {
        $extractedValue = $str;

        preg_match_all("/\%(.+?)\%/", $str, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($data as $formFieldName => $formFieldValue) {
                    if ($formFieldName == $inputValue) {
                        $str = str_replace($matches[0][$key], $formFieldValue['value'], $str);
                    }
                }

                //replace with '' if not found.
                $extractedValue = str_replace($matches[0][$key], '', $str);
            }
        }

        //remove invalid commas
        $extractedValue = trim(implode(',', preg_split('@,@', $extractedValue, NULL, PREG_SPLIT_NO_EMPTY)));

        return $extractedValue;
    }

    /**
     * @param bool $asArray
     *
     * @return array|string
     */
    public function getMessages($asArray = TRUE)
    {
        return $asArray ? $this->messages : implode(',', $this->messages);
    }

    /**
     * @param string $message
     */
    private function log($message = '')
    {
        $this->messages[] = $message;
    }
}
