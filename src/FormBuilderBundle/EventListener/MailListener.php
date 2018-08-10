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
     *
     * @throws \Exception
     */
    public function onFormSubmit(SubmissionEvent $event)
    {
        $request = $event->getRequest();
        $form = $event->getForm();
        $formId = $form->getData()->getId();

        $formConfiguration = $event->getFormConfiguration();
        $emailConfiguration = null;

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
            $this->getFlashBag()->add('formbuilder_' . $formId . '_error', 'error while sending mail: ' . $e->getMessage());
        }

        $this->onSuccess($event, $form, $request->getLocale());
    }

    /**
     * @param int           $mailTemplateId
     * @param array         $userOptions
     * @param FormInterface $form
     * @param string        $locale
     * @param bool          $isCopy
     *
     * @return bool
     * @throws \Exception
     */
    private function sendForm($mailTemplateId = 0, $userOptions, FormInterface $form, $locale, $isCopy = false)
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

        $mail = $this->mailParser->create($mailTemplate, $form, $locale);

        $mail->setParam('_form_builder_id', (int)$form->getData()->getId());
        $mail->setParam('_form_builder_is_copy', $isCopy ? 1 : 0);
        $mail->setParam('_form_builder_preset', $userOptions['form_preset'] === 'custom' ? null : $userOptions['form_preset']);

        $mailEvent = new MailEvent($form, $mail, $userOptions, $isCopy);
        \Pimcore::getEventDispatcher()->dispatch(FormBuilderEvents::FORM_MAIL_PRE_SUBMIT, $mailEvent);

        $mail = $mailEvent->getEmail();
        $mail->send();

        return true;
    }

    /**
     * @param SubmissionEvent   $event
     * @param FormInterface     $form
     * @param                   $locale
     *
     * @return bool
     * @throws \Exception
     */
    private function onSuccess(SubmissionEvent $event, FormInterface $form, $locale)
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
                $this->getFlashBag()->add('formbuilder_redirect_flash_message', $successConditionData->getFlashMessage($locale));
            }
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
            if (!$this->getFlashBag()->has('formbuilder_redirect_flash_message')) {
                $redirectFlashMessage = $mailTemplate->getProperty('mail_successfully_sent_flash_message');
                if (!is_null($redirectFlashMessage)) {
                    $this->getFlashBag()->add('formbuilder_redirect_flash_message', $redirectFlashMessage);
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

        $this->getFlashBag()->add($error ? 'formbuilder_' . $formId . '_error' : 'formbuilder_' . $formId . '_success', $message);
    }

    /**
     * @param FormInterface $form
     * @param string        $dispatchModule
     * @param array         $moduleOptions
     *
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

    /**
     * @return FlashBagInterface
     */
    private function getFlashBag()
    {
        return $this->session->getFlashBag();
    }
}
