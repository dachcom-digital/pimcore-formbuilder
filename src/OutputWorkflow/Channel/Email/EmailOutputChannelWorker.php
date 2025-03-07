<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelContext;
use FormBuilderBundle\OutputWorkflow\Channel\Email\Parser\MailParser;
use Pimcore\Mail;
use Pimcore\Model\Document;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EmailOutputChannelWorker
{
    public function __construct(
        protected Configuration $configuration,
        protected MailParser $mailParser,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(FormInterface $form, array $channelConfiguration, array $formRuntimeData, string $workflowName, array $context = []): void
    {
        /** @var FormDataInterface $formData */
        $formData = $form->getData();

        $mailTemplate = $channelConfiguration['mailTemplate'];
        $forcePlainText = $channelConfiguration['forcePlainText'];
        $disableDefaultMailBody = $channelConfiguration['disableDefaultMailBody'];
        $disableMailLogging = $channelConfiguration['disableMailLogging'] ?? false;

        $doubleOptInSession = $context['doubleOptInSession'] ?? null;
        $channelContext = $context['channelContext'] ?? null;

        $mailTemplateId = $mailTemplate['id'];
        $mailTemplate = is_numeric($mailTemplateId) ? Document\Email::getById($mailTemplateId) : null;

        if (!$mailTemplate instanceof Document\Email) {
            throw new \Exception('Invalid Email Document Id: ' . $mailTemplateId);
        }

        $mail = $this->mailParser->create($mailTemplate, $form, $channelConfiguration, $context);
        $forceSubmissionAsPlainText = (bool) $forcePlainText;

        $mail->setParam('_form_builder_output_workflow_name', $workflowName);
        $mail->setParam('_form_builder_id', (int) $formData->getFormDefinition()->getId());
        $mail->setParam('_form_builder_preset', $formRuntimeData['form_preset'] === 'custom' ? null : $formRuntimeData['form_preset']);

        if ($doubleOptInSession instanceof DoubleOptInSessionInterface) {
            $mail->setParam('_form_builder_double_opt_in_token', $doubleOptInSession->getTokenAsString());
            $mail->setParam('_form_builder_double_opt_in_session_email', $doubleOptInSession->getEmail());
        }

        if ($disableDefaultMailBody === true) {
            $mail->setParam('_form_builder_disabled_default_mail_body', 1);
        }

        if ($disableMailLogging === true) {
            $mail->disableLogging();
        }

        // dispatch subject guard event
        if (null === $mail = $this->dispatchGuardEvent($form->getData(), $mail, $workflowName, $formRuntimeData, $channelContext)) {
            return;
        }

        // set html2text options
        $html2textOptions = $this->configuration->getConfig('email');
        if (isset($html2textOptions['html_2_text_options']) && count($html2textOptions['html_2_text_options']) > 0) {
            $mail->setHtml2TextOptions($html2textOptions['html_2_text_options']);
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
        $mail->subject($mail->getSubjectRendered());
        $bodyTextRendered = $mail->getBodyTextRendered();

        if ($bodyTextRendered) {
            $mail->text($bodyTextRendered, 'utf-8');
        }

        $mail->sendWithoutRendering();
    }

    protected function sendDefault(Mail $mail): void
    {
        $mail->send();
    }

    /**
     * @throws GuardException
     * @throws GuardOutputWorkflowException
     */
    protected function dispatchGuardEvent(
        FormDataInterface $formData,
        Mail $subject,
        string $workflowName,
        array $formRuntimeData,
        ?ChannelContext $channelContext
    ): ?Mail {
        $channelSubjectGuardEvent = new ChannelSubjectGuardEvent(
            $formData,
            $subject,
            $workflowName,
            'email',
            $formRuntimeData,
            $channelContext
        );

        $this->eventDispatcher->dispatch($channelSubjectGuardEvent, FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH);

        if ($channelSubjectGuardEvent->isSuspended()) {
            return null;
        }

        if ($channelSubjectGuardEvent->shouldStopChannel()) {
            throw new GuardChannelException($channelSubjectGuardEvent->getFailMessage());
        }

        if ($channelSubjectGuardEvent->shouldStopOutputWorkflow()) {
            throw new GuardOutputWorkflowException($channelSubjectGuardEvent->getFailMessage());
        }

        return $channelSubjectGuardEvent->getSubject();
    }
}
