<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use Pimcore\Mail;
use Pimcore\Model\Document;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Event\MailEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Session\FlashBagManagerInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Email\Parser\MailParser;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\MailBehaviourData;

class EmailOutputChannelWorker
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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param FlashBagManagerInterface $flashBagManager
     * @param MailParser               $mailParser
     * @param IncludeRenderer          $includeRenderer
     * @param Dispatcher               $dispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FlashBagManagerInterface $flashBagManager,
        MailParser $mailParser,
        IncludeRenderer $includeRenderer,
        Dispatcher $dispatcher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->flashBagManager = $flashBagManager;
        $this->mailParser = $mailParser;
        $this->includeRenderer = $includeRenderer;
        $this->dispatcher = $dispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param FormInterface $form
     * @param array         $channelConfiguration
     * @param array         $formRuntimeOptions
     * @param string        $workflowName
     * @param string        $locale
     *
     * @throws \Exception
     */
    public function process(FormInterface $form, $channelConfiguration, array $formRuntimeOptions, string $workflowName, string $locale)
    {
        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        $mailTemplate = $channelConfiguration['mailTemplate'];
        $forcePlainText = $channelConfiguration['forcePlainText'];
        $allowAttachments = $channelConfiguration['allowAttachments'];

        $hasIsCopyFlag = isset($channelConfiguration['legacyIsCopy']);
        $isCopy = $hasIsCopyFlag && $channelConfiguration['legacyIsCopy'] === true;

        $mailTemplateId = $mailTemplate['id'];

        /** @var MailBehaviourData $mailConditionData */
        $mailConditionData = $this->checkMailCondition($formData, 'mail_behaviour', ['isCopy' => $isCopy]);

        if ($mailConditionData->hasMailTemplate()) {
            $conditionalMailTemplateId = $mailConditionData->getMailTemplateId($locale);
            $mailTemplate = is_numeric($conditionalMailTemplateId) ? Document\Email::getById($conditionalMailTemplateId) : null;
        } else {
            $mailTemplate = is_numeric($mailTemplateId) ? Document\Email::getById($mailTemplateId) : null;
        }

        if (!$mailTemplate instanceof Document\Email) {
            throw new \Exception('Invalid Email Document Id: ' . $mailTemplateId);
        }

        if ($mailConditionData->hasRecipient()) {
            $mailTemplate->setTo($mailConditionData->getRecipient());
        }

        $attachments = [];
        if ($formData->hasAttachments() && $allowAttachments === false) {
            $attachments = $formData->getAttachments();
        }

        $mail = $this->mailParser->create($mailTemplate, $form, $attachments, $channelConfiguration, $locale);
        $forceSubmissionAsPlainText = (bool) $forcePlainText;

        if ($hasIsCopyFlag === true) {
            $mail->setParam('_form_builder_is_copy', $isCopy ? 1 : 0);
        } else {
            $mail->setParam('_form_builder_output_workflow_name', $workflowName);
        }

        $mail->setParam('_form_builder_id', (int) $formData->getFormDefinition()->getId());
        $mail->setParam('_form_builder_preset', $formRuntimeOptions['form_preset'] === 'custom' ? null : $formRuntimeOptions['form_preset']);

        // dispatch legacy event
        $mailEvent = new MailEvent($form, $mail, $formRuntimeOptions, $isCopy);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_MAIL_PRE_SUBMIT, $mailEvent);
        $mail = $mailEvent->getEmail();

        // dispatch subject guard event
        if (null === $mail = $this->dispatchGuardEvent($form->getData(), $mail, $workflowName, $formRuntimeOptions)) {
            return;
        }

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
    }

    /**
     * @param Mail $mail
     *
     * @throws \Exception
     */
    protected function sendPlainTextOnly(Mail $mail)
    {
        $mail->setSubject($mail->getSubjectRendered());
        $bodyTextRendered = $mail->getBodyTextRendered();

        if ($bodyTextRendered) {
            $mail->setBody($bodyTextRendered, 'text/plain');
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
     * @param FormDataInterface $formData
     * @param string            $dispatchModule
     * @param array             $moduleOptions
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    protected function checkMailCondition(FormDataInterface $formData, $dispatchModule, $moduleOptions = [])
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'         => $formData->getData(),
            'conditionalLogic' => $formData->getFormDefinition()->getConditionalLogic()
        ], $moduleOptions);
    }

    /**
     * @param FormDataInterface $formData
     * @param Mail              $subject
     * @param string            $workflowName
     * @param array             $formRuntimeOptions
     *
     * @return Mail|null
     *
     * @throws GuardException
     */
    protected function dispatchGuardEvent(FormDataInterface $formData, Mail $subject, string $workflowName, array $formRuntimeOptions)
    {
        $channelSubjectGuardEvent = new ChannelSubjectGuardEvent($formData, $subject, $workflowName, 'email', $formRuntimeOptions);
        $this->eventDispatcher->dispatch(FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH, $channelSubjectGuardEvent);

        if ($channelSubjectGuardEvent->isSuspended()) {
            return null;
        }

        if ($channelSubjectGuardEvent->shouldStopChannel()) {
            throw new GuardChannelException($channelSubjectGuardEvent->getFailMessage());
        } elseif ($channelSubjectGuardEvent->shouldStopOutputWorkflow()) {
            throw new GuardOutputWorkflowException($channelSubjectGuardEvent->getFailMessage());
        }

        return $channelSubjectGuardEvent->getSubject();
    }
}