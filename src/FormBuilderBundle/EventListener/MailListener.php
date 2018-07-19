<?php

namespace FormBuilderBundle\EventListener;

use FormBuilderBundle\Event\MailEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Parser\MailParser;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\MailBehaviourData;
use Pimcore\Model\Document;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MailListener implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var MailParser
     */
    protected $mailParser;

    /**
     * @var IncludeRenderer
     */
    protected $includeRenderer;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * MailListener constructor.
     *
     * @param SessionInterface $session
     * @param MailParser       $mailParser
     * @param IncludeRenderer  $includeRenderer
     * @param Dispatcher       $dispatcher
     */
    public function __construct(
        SessionInterface $session,
        MailParser $mailParser,
        IncludeRenderer $includeRenderer,
        Dispatcher $dispatcher
    ) {
        $this->session = $session;
        $this->mailParser = $mailParser;
        $this->includeRenderer = $includeRenderer;
        $this->dispatcher = $dispatcher;
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
        $flashBag = $this->session->getFlashBag();
        $emailConfiguration = null;

        try {

            if (empty($formConfiguration)) {
                throw new \Exception('no valid mail configuration given.');
            }

            $emailConfiguration = $formConfiguration['email'];
            $sendCopy = $emailConfiguration['send_copy'];

            $send = $this->sendForm($emailConfiguration['mail_template_id'], $form, $request->getLocale());
            if ($send === true) {
                //send copy!
                if ($sendCopy === true) {
                    if (empty($emailConfiguration['copy_mail_template_id'])) {
                        throw new \Exception('no valid copy mail template given.');
                    }
                    $send = $this->sendForm($emailConfiguration['copy_mail_template_id'], $form, $request->getLocale(), true);
                    if ($send !== true) {
                        throw new \Exception('copy mail not sent.');
                    }
                }
            } else {
                throw new \Exception('mail not sent.');
            }
        } catch (\Exception $e) {
            $flashBag->add('error', 'error while sending mail: ' . $e->getMessage());
        }

        $this->onSuccess($event, $flashBag, $emailConfiguration['mail_template_id']);
    }

    /**
     * @param int           $mailTemplateId
     * @param FormInterface $form
     * @param               $locale
     * @param bool          $isCopy
     * @return bool
     * @throws \Exception
     */
    private function sendForm($mailTemplateId = 0, FormInterface $form, $locale, $isCopy = false)
    {
        /** @var MailBehaviourData $mailConditionData */
        $mailConditionData = $this->checkMailCondition($form, $isCopy);

        if ($mailConditionData->hasMailTemplate()) {
            $mailTemplate = Document\Email::getById($mailConditionData->getMailTemplateId());
        } else {
            $mailTemplate = Document\Email::getById($mailTemplateId);
        }

        if (!$mailTemplate instanceof Document\Email) {
            return false;
        }

        if ($mailConditionData->hasRecipient()) {
            $mailTemplate->setTo($mailConditionData->getRecipient());
        }

        $mail = $this->mailParser->create($mailTemplate, $form, $locale, $isCopy);

        $mailEvent = new MailEvent($form, $mail);
        \Pimcore::getEventDispatcher()->dispatch(FormBuilderEvents::FORM_MAIL_PRE_SUBMIT, $mailEvent);

        $mail = $mailEvent->getEmail();
        $mail->send();

        return true;
    }

    /**
     * @param FormInterface $form
     * @param               $isCopy
     * @return DataInterface
     * @throws \Exception
     */
    private function checkMailCondition(FormInterface $form, $isCopy)
    {
        return $this->dispatcher->runFormDispatcher('mail_behaviour', [
            'formData'         => $form->getData()->getData(),
            'conditionalLogic' => $form->getData()->getConditionalLogic()
        ], [
            'isCopy' => $isCopy
        ]);
    }

    /**
     * @param SubmissionEvent   $event
     * @param FlashBagInterface $flashBag
     * @param                   $mailTemplateId
     *
     * @return bool
     */
    private function onSuccess(SubmissionEvent $event, $flashBag, $mailTemplateId)
    {
        $error = false;
        $message = 'Success!';

        $mailTemplate = Document\Email::getById($mailTemplateId);
        if (!$mailTemplate instanceof Document\Email) {
            return false;
        }

        $afterSuccess = $mailTemplate->getProperty('mail_successfully_sent');

        //get the content from a snippet
        if ($afterSuccess instanceof Document\Snippet) {
            $params['document'] = $afterSuccess;

            try {
                $message = $this->includeRenderer->render($afterSuccess, $params, false);
            } catch (\Exception $e) {
                $error = true;
                $message = $e->getMessage();
            }
        } //it's a redirect!
        elseif ($afterSuccess instanceof Document) {
            $message = $afterSuccess->getFullPath();
            $event->setRedirectUri($afterSuccess->getFullPath());
        } //it's just a string!
        elseif (is_string($afterSuccess)) {
            $message = $afterSuccess;
        }

        $flashBag->add($error ? 'error' : 'success', $message);
    }
}
