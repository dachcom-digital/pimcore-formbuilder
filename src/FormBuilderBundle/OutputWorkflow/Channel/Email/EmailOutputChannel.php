<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\EmailChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\Tool\LocaleDataMapper;

class EmailOutputChannel implements ChannelInterface
{
    protected EmailOutputChannelWorker $channelWorker;
    protected LocaleDataMapper $localeDataMapper;

    public function __construct(EmailOutputChannelWorker $channelWorker, LocaleDataMapper $localeDataMapper)
    {
        $this->channelWorker = $channelWorker;
        $this->localeDataMapper = $localeDataMapper;
    }

    public function getFormType(): string
    {
        return EmailChannelType::class;
    }

    public function isLocalizedConfiguration(): bool
    {
        return true;
    }

    public function getUsedFormFieldNames(array $channelConfiguration): array
    {
        // Unsupported for EmailOutputChanel

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        $locale = $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();
        $formRuntimeData = $submissionEvent->getFormRuntimeData();

        $localizedConfig = $this->validateOutputConfig($channelConfiguration, $locale);

        $this->channelWorker->process($form, $localizedConfig, $formRuntimeData, $workflowName, $locale);
    }

    /**
     * @throws \Exception
     */
    protected function validateOutputConfig(array $channelConfiguration, string $locale): array
    {
        $localizedConfig = $this->localeDataMapper->mapMultiDimensional($locale, 'mailTemplate', true, $channelConfiguration);

        $message = null;
        if (!isset($localizedConfig['mailTemplate'])) {
            $message = 'No mail template definition available.';
        } elseif ($localizedConfig['mailTemplate']['id'] === null) {
            $message = 'No mail template id available.';
        }

        if ($message === null) {
            return $localizedConfig;
        }

        throw new \Exception($message);
    }
}
