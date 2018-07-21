<?php

namespace FormBuilderBundle\EventListener;

use FormBuilderBundle\Event\MailEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Parser\MailParser;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\MailBehaviourData;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\SuccessMessageData;
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
     * Defines which mail template should be used
     * to gather success message context.
     * By default this is always the main mail template
     * but can be changed via mail behaviour CL.
     *
     * @var int
     */
    private $successMailTemplateProviderId;

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
     * @throws \Exception
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        $request = $event->getRequest();
        $form = $event->getForm();

        $formConfiguration = $event->getFormConfiguration();
        /** @var FlashBagInterface $flashBag */
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

        $this->onSuccess($event, $form, $flashBag, $request->getLocale());
    }

    /**
     * @param int           $mailTemplateId
     * @param FormInterface $form
     * @param string        $locale
     * @param bool          $isCopy
     * @return bool
     * @throws \Exception
     */
    private function sendForm($mailTemplateId = 0, FormInterface $form, $locale, $isCopy = false)
    {
        /** @var MailBehaviourData $mailConditionData */
        $mailConditionData = $this->checkMailCondition($form, 'mail_behaviour', ['isCopy' => $isCopy]);

        if ($mailConditionData->hasMailTemplate()) {
            $conditionalMailTemplateId = $mailConditionData->getMailTemplateId($locale);
            $mailTemplate = Document\Email::getById($conditionalMailTemplateId);

        } else {
            $mailTemplate = Document\Email::getById($mailTemplateId);
        }

        if (!$mailTemplate instanceof Document\Email) {
            return false;
        }

        if ($isCopy === false) {
            $this->successMailTemplateProviderId = $mailTemplate->getId();
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
     * @param SubmissionEvent   $event
     * @param FormInterface     $form
     * @param FlashBagInterface $flashBag
     * @param                   $locale
     * @return bool
     * @throws \Exception
     */
    private function onSuccess(SubmissionEvent $event, FormInterface $form, FlashBagInterface $flashBag, $locale)
    {
        $error = false;
        $message = 'Success!';

        $mailTemplate = Document\Email::getById($this->successMailTemplateProviderId);
        if (!$mailTemplate instanceof Document\Email) {
            return false;
        }

        /** @var SuccessMessageData $successConditionData */
        $successConditionData = $this->checkMailCondition($form, 'success_message');

        if ($successConditionData->hasData()) {
            $afterSuccess = $successConditionData->getIdentifiedData($locale);
        } else {
            $afterSuccess = $mailTemplate->getProperty('mail_successfully_sent');
        }

        if ($afterSuccess instanceof Document\Snippet) {
            $params['document'] = $afterSuccess;
            try {
                $message = $this->includeRenderer->render($afterSuccess, $params, false);
            } catch (\Exception $e) {
                $error = true;
                $message = $e->getMessage();
            }
        } elseif ($afterSuccess instanceof Document) {
            $message = $afterSuccess->getFullPath();
            $event->setRedirectUri($afterSuccess->getFullPath());
        } elseif (is_string($afterSuccess)) {
            $message = $afterSuccess;
        }

        $flashBag->add($error ? 'error' : 'success', $message);
    }

    /**
     * @param FormInterface $form
     * @param string        $dispatchModule
     * @param array         $moduleOptions
     * @return DataInterface
     * @throws \Exception
     */
    private function checkMailCondition(FormInterface $form, $dispatchModule, $moduleOptions = [])
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'         => $form->getData()->getData(),
            'conditionalLogic' => $form->getData()->getConditionalLogic()
        ], $moduleOptions);
    }
}
