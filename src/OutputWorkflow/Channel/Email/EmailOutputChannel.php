<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Email;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\EmailChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\Tool\LocaleDataMapper;

class EmailOutputChannel implements ChannelInterface
{
    public function __construct(
        protected EmailOutputChannelWorker $channelWorker,
        protected LocaleDataMapper $localeDataMapper
    ) {
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
        $locale = $submissionEvent->getLocale() ?? $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();
        $formRuntimeData = $submissionEvent->getFormRuntimeData();

        $localizedConfig = $this->validateOutputConfig($channelConfiguration, $locale);

        $context = [
            'locale'             => $locale,
            'doubleOptInSession' => $submissionEvent->getDoubleOptInSession(),
        ];

        $this->channelWorker->process($form, $localizedConfig, $formRuntimeData, $workflowName, $context);
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
