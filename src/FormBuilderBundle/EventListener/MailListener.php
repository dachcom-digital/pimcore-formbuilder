<?php

namespace FormBuilderBundle\EventListener;

use FormBuilderBundle\Event\MailEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Parser\MailParser;
use FormBuilderBundle\Session\FlashBagManagerInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\MailBehaviourData;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\SuccessMessageData;
use Pimcore\Mail;
use Pimcore\Model\Document;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Storage\FormInterface as FormBuilderFormInterface;

class MailListener implements EventSubscriberInterface
{
    /**
     * @var FlashBagManagerInterface
     */
    protected $flashBagManager;

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
     * @param FlashBagManagerInterface $flashBagManager
     * @param MailParser               $mailParser
     * @param IncludeRenderer          $includeRenderer
     * @param Dispatcher               $dispatcher
     */
    public function __construct(
        FlashBagManagerInterface $flashBagManager,
        MailParser $mailParser,
        IncludeRenderer $includeRenderer,
        Dispatcher $dispatcher
    ) {
        $this->flashBagManager = $flashBagManager;
        $this->mailParser = $mailParser;
        $this->includeRenderer = $includeRenderer;
        $this->dispatcher = $dispatcher;
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
     *
     * @throws \Exception
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        $request = $event->getRequest();
        $form = $event->getForm();
        /** @var FormBuilderFormInterface $data */
        $data = $form->getData();
        $formId = $data->getId();
        $formConfiguration = $event->getFormConfiguration();

        try {
            if (empty($formConfiguration)) {
                throw new \Exception('no valid mail configuration given.');
            }

            $emailConfiguration = $formConfiguration['email'];
            $userOptions = $formConfiguration['user_options'];
            $sendCopy = $emailConfiguration['send_copy'];

            $send = $this->sendForm($emailConfiguration['mail_template_id'], $userOptions, $form, $request->getLocale());
            if ($send === true) {
                if ($sendCopy === true) {
                    if (empty($emailConfiguration['copy_mail_template_id'])) {
                        throw new \Exception('no valid copy mail template given.');
                    }
                    $send = $this->sendForm($emailConfiguration['copy_mail_template_id'], $userOptions, $form, $request->getLocale(), true);
                    if ($send !== true) {
                        throw new \Exception('copy mail not sent.');
                    }
                }
            } else {
                throw new \Exception('mail not sent.');
            }
        } catch (\Exception $e) {
            $this->flashBagManager->add('formbuilder_' . $formId . '_error', 'error while sending mail: ' . $e->getMessage());
        }

        $this->onSuccess($event, $form, $request->getLocale());
    }

    /**
     * @param int|null      $mailTemplateId
     * @param array         $userOptions
     * @param FormInterface $form
     * @param string        $locale
     * @param bool          $isCopy
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function sendForm($mailTemplateId, $userOptions, FormInterface $form, $locale, $isCopy = false)
    {
        /** @var FormBuilderFormInterface $data */
        $data = $form->getData();

        /** @var MailBehaviourData $mailConditionData */
        $mailConditionData = $this->checkMailCondition($form, 'mail_behaviour', ['isCopy' => $isCopy]);

        if ($mailConditionData->hasMailTemplate()) {
            $conditionalMailTemplateId = $mailConditionData->getMailTemplateId($locale);
            $mailTemplate = is_numeric($conditionalMailTemplateId) ? Document\Email::getById($conditionalMailTemplateId) : null;
        } else {
            $mailTemplate = is_numeric($mailTemplateId) ? Document\Email::getById($mailTemplateId) : null;
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

        $attachments = [];
        if ($data->hasAttachments() && $isCopy === false) {
            $attachments = $data->getAttachments();
        }

        $mail = $this->mailParser->create($mailTemplate, $form, $attachments, $locale, $isCopy);
        $forceSubmissionAsPlainText = (bool) $mailTemplate->getProperty('mail_force_plain_text');

        $mail->setParam('_form_builder_id', (int) $data->getId());
        $mail->setParam('_form_builder_is_copy', $isCopy ? 1 : 0);
        $mail->setParam('_form_builder_preset', $userOptions['form_preset'] === 'custom' ? null : $userOptions['form_preset']);

        $mailEvent = new MailEvent($form, $mail, $userOptions, $isCopy);
        \Pimcore::getEventDispatcher()->dispatch(FormBuilderEvents::FORM_MAIL_PRE_SUBMIT, $mailEvent);

        $mail = $mailEvent->getEmail();

        if ($mail::getHtml2textInstalled()) {
            $mail->enableHtml2textBinary();
        }

        if ($forceSubmissionAsPlainText === true && $mail::determineHtml2TextIsInstalled() === false) {
            throw new \Exception('trying to enable html2text binary, but html2text is not installed!');
        }

        if ($forceSubmissionAsPlainText === true) {
            $this->sendPlainTextOnly($mail);
        } else {
            $this->sendDefault($mail);
        }

        return true;
    }

    /**
     * @param Mail $mail
     */
    protected function sendPlainTextOnly(Mail $mail)
    {
        $mail->setSubject($mail->getSubjectRendered());
        $bodyTextRendered = $mail->getBodyTextRendered();

        if ($bodyTextRendered) {
            $mail->addPart($bodyTextRendered, 'text/plain');
        }

        $mail->sendWithoutRendering();
    }

    /**
     * @param Mail $mail
     */
    protected function sendDefault(Mail $mail)
    {
        $mail->send();
    }

    /**
     * @param SubmissionEvent $event
     * @param FormInterface   $form
     * @param string          $locale
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function onSuccess(SubmissionEvent $event, FormInterface $form, $locale)
    {
        $formId = $form->getData()->getId();
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
            if ($successConditionData->hasFlashMessage()) {
                $this->flashBagManager->add('formbuilder_redirect_flash_message', $successConditionData->getFlashMessage($locale));
            }
        } else {
            $afterSuccess = $mailTemplate->getProperty('mail_successfully_sent');
        }

        $params = [];
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
            if (!$this->flashBagManager->has('formbuilder_redirect_flash_message')) {
                $redirectFlashMessage = $mailTemplate->getProperty('mail_successfully_sent_flash_message');
                if (!is_null($redirectFlashMessage)) {
                    $this->flashBagManager->add('formbuilder_redirect_flash_message', $redirectFlashMessage);
                }
            }
        } elseif (is_string($afterSuccess)) {
            // maybe it's a external redirect
            if (substr($afterSuccess, 0, 4) === 'http') {
                $event->setRedirectUri($afterSuccess);
            } else {
                $message = $afterSuccess;
            }
        }

        $this->flashBagManager->add($error ? 'formbuilder_' . $formId . '_error' : 'formbuilder_' . $formId . '_success', $message);
    }

    /**
     * @param FormInterface $form
     * @param string        $dispatchModule
     * @param array         $moduleOptions
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    protected function checkMailCondition(FormInterface $form, $dispatchModule, $moduleOptions = [])
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'         => $form->getData()->getData(),
            'conditionalLogic' => $form->getData()->getConditionalLogic()
        ], $moduleOptions);
    }
}
