<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflow;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Email;

class OutputWorkflowResolver implements OutputWorkflowResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(SubmissionEvent $submissionEvent)
    {
        $form = $submissionEvent->getForm();

        /** @var FormDataInterface $data */
        $data = $form->getData();
        $formDefinition = $data->getFormDefinition();

        $formRuntimeData = $submissionEvent->getFormRuntimeData();
        $userSelectedOutputWorkflow = isset($formRuntimeData['form_output_workflow']) ? $formRuntimeData['form_output_workflow'] : null;

        if ($formDefinition->hasOutputWorkflows() === false) {
            return $this->buildFallBackWorkflow($formDefinition, $formRuntimeData);
        }

        $outputWorkflow = null;
        $outputWorkflows = $formDefinition->getOutputWorkflows();

        if ($userSelectedOutputWorkflow === null && $outputWorkflows->count() === 1) {
            return $outputWorkflows->first();
        } elseif ($userSelectedOutputWorkflow !== null) {
            $selectedOutputWorkflows = $outputWorkflows->filter(function (OutputWorkflowInterface $outputWorkflow) use ($userSelectedOutputWorkflow) {
                return is_numeric($userSelectedOutputWorkflow)
                    ? $outputWorkflow->getId() === $userSelectedOutputWorkflow
                    : $outputWorkflow->getName() === $userSelectedOutputWorkflow;
            });

            return $selectedOutputWorkflows->count() === 1 ? $selectedOutputWorkflows->first() : null;
        }

        return null;
    }

    /**
     * @param FormDefinitionInterface $formDefinition
     * @param array                   $formRuntimeData
     *
     * @return OutputWorkflow|null
     */
    protected function buildFallBackWorkflow(FormDefinitionInterface $formDefinition, array $formRuntimeData)
    {
        if (!isset($formRuntimeData['email'])) {
            return null;
        }

        $sendCopy = $formRuntimeData['email']['send_copy'];
        $mailTemplateId = $formRuntimeData['email']['mail_template_id'];
        $copyMailTemplateId = $formRuntimeData['email']['copy_mail_template_id'];

        $mailTemplate = Email::getById($mailTemplateId);
        if (!$mailTemplate instanceof Email) {
            return null;
        }

        $fallbackWorkflow = new OutputWorkflow();
        $fallbackWorkflow->setFormDefinition($formDefinition);
        $fallbackWorkflow->setName(sprintf('Fallback Workflow "%s"', $formDefinition->getName()));

        $fallbackWorkflow->setSuccessManagement($this->buildFallbackSuccessManagement($mailTemplate));

        $defaultChannel = $this->buildFallbackWorkflowChannel($mailTemplate, false);
        $defaultChannel->setOutputWorkflow($fallbackWorkflow);
        $fallbackWorkflow->addChannel($defaultChannel);

        if ($sendCopy === false) {
            return $fallbackWorkflow;
        }

        $copyMailTemplate = Email::getById($copyMailTemplateId);
        if (!$copyMailTemplate instanceof Email) {
            return null;
        }

        $copyChannel = $this->buildFallbackWorkflowChannel($copyMailTemplate, true);
        $copyChannel->setOutputWorkflow($fallbackWorkflow);
        $fallbackWorkflow->addChannel($copyChannel);

        return $fallbackWorkflow;
    }

    /**
     * @param Email $email
     * @param bool  $isCopy
     *
     * @return OutputWorkflowChannel
     */
    protected function buildFallbackWorkflowChannel(Email $email, bool $isCopy)
    {
        $ignoreFields = (string) $email->getProperty('mail_ignore_fields');
        $ignoreFields = array_map('trim', explode(',', $ignoreFields));

        $defaultChannel = new OutputWorkflowChannel();
        $defaultChannel->setType('email');
        $defaultChannel->setConfiguration([
            'default' => [
                'mailTemplate'           => [
                    'id'      => $email->getId(),
                    'path'    => $email->getFullPath(),
                    'type'    => 'document',
                    'subtype' => 'email',
                ],
                'mailLayoutData'         => null,
                'legacyIsCopy'           => $isCopy,
                'allowAttachments'       => $isCopy === false,
                'ignoreFields'           => $ignoreFields,
                'forcePlainText'         => (bool) $email->getProperty('mail_force_plain_text'),
                'disableDefaultMailBody' => (bool) $email->getProperty('mail_disable_default_mail_body'),
            ]
        ]);

        return $defaultChannel;
    }

    /**
     * @param Email $email
     *
     * @return null|array
     */
    protected function buildFallbackSuccessManagement(Email $email)
    {
        $type = null;
        $value = null;
        $extraField = [];

        $afterSuccessData = $email->getProperty('mail_successfully_sent');

        if ($afterSuccessData instanceof Document\Snippet) {
            $type = 'snippet';
            $value = [
                'default' => [
                    'id'      => $afterSuccessData->getId(),
                    'path'    => $afterSuccessData->getFullPath(),
                    'type'    => 'document',
                    'subtype' => 'snippet',
                ]
            ];
        } elseif ($afterSuccessData instanceof Document) {
            $type = 'redirect';
            $value = [
                'default' => [
                    'id'      => $afterSuccessData->getId(),
                    'path'    => $afterSuccessData->getFullPath(),
                    'type'    => 'document',
                    'subtype' => 'page',
                ]
            ];

            $flashMessage = $email->getProperty('mail_successfully_sent_flash_message');
            if (!empty($flashMessage)) {
                $extraField = ['flashMessage' => $flashMessage];
            }
        } elseif (is_string($afterSuccessData)) {
            $type = substr($afterSuccessData, 0, 4) === 'http' ? 'redirect_external' : 'string';
            $value = $afterSuccessData;
        } else {
            $type = 'string';
            $value = 'Success!';
        }

        if ($type === null) {
            return [];
        }

        return array_merge([
            'identifier' => $type,
            'value'      => $value
        ], $extraField);
    }
}
