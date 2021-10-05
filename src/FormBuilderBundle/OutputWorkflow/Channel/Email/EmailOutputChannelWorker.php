<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use Pimcore\Mail;
use Pimcore\Model\Document;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\OutputWorkflow\Channel\Email\Parser\MailParser;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\MailBehaviourData;

class EmailOutputChannelWorker
{
    protected MailParser $mailParser;
    protected Dispatcher $dispatcher;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        MailParser $mailParser,
        Dispatcher $dispatcher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->mailParser = $mailParser;
        $this->dispatcher = $dispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \Exception
     */
    public function process(FormInterface $form, array $channelConfiguration, array $formRuntimeData, string $workflowName, string $locale): void
    {
        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        $mailTemplate = $channelConfiguration['mailTemplate'];
        $forcePlainText = $channelConfiguration['forcePlainText'];

        $hasIsCopyFlag = isset($channelConfiguration['legacyIsCopy']);
        $isCopy = $hasIsCopyFlag && $channelConfiguration['legacyIsCopy'] === true;

        $mailTemplateId = $mailTemplate['id'];

        /** @var MailBehaviourData $mailConditionData */
        $mailConditionData = $this->checkMailCondition('mail_behaviour', $formData, $formRuntimeData, ['isCopy' => $isCopy]);

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

        $mail = $this->mailParser->create($mailTemplate, $form, $channelConfiguration, $locale);
        $forceSubmissionAsPlainText = (bool) $forcePlainText;

        if ($hasIsCopyFlag === true) {
            $mail->setParam('_form_builder_is_copy', $isCopy ? 1 : 0);
        } else {
            $mail->setParam('_form_builder_output_workflow_name', $workflowName);
        }

        $mail->setParam('_form_builder_id', (int) $formData->getFormDefinition()->getId());
        $mail->setParam('_form_builder_preset', $formRuntimeData['form_preset'] === 'custom' ? null : $formRuntimeData['form_preset']);

        // dispatch subject guard event
        if (null === $mail = $this->dispatchGuardEvent($form->getData(), $mail, $workflowName, $formRuntimeData)) {
            return;
        }

        if ($forceSubmissionAsPlainText === true) {
            $this->sendPlainTextOnly($mail);
        } else {
            $this->sendDefault($mail);
        }
    }

    /**
     * @throws \Exception
     */
    protected function sendPlainTextOnly(Mail $mail): void
    {
        $mail->setSubject($mail->getSubjectRendered());
        $bodyTextRendered = $mail->getBodyTextRendered();

        if ($bodyTextRendered) {
            $mail->text($bodyTextRendered, 'text/plain');
        }

        $mail->sendWithoutRendering();
    }

    protected function sendDefault(Mail $mail): void
    {
        $mail->send();
    }

    /**
     * @throws \Exception
     */
    protected function checkMailCondition(string $dispatchModule, FormDataInterface $formData, array $formRuntimeOptions, array $moduleOptions = []): DataInterface
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'           => $formData->getData(),
            'conditionalLogic'   => $formData->getFormDefinition()->getConditionalLogic(),
            'formRuntimeOptions' => $formRuntimeOptions
        ], $moduleOptions);
    }

    /**
     * @throws GuardException
     */
    protected function dispatchGuardEvent(FormDataInterface $formData, Mail $subject, string $workflowName, array $formRuntimeData): ?Mail
    {
        $channelSubjectGuardEvent = new ChannelSubjectGuardEvent($formData, $subject, $workflowName, 'email', $formRuntimeData);
        $this->eventDispatcher->dispatch($channelSubjectGuardEvent, FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH);

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
