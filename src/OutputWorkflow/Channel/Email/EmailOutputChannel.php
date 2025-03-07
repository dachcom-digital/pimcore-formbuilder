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

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\EmailChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelContextAwareInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Trait\ChannelContextTrait;
use FormBuilderBundle\Tool\LocaleDataMapper;

class EmailOutputChannel implements ChannelInterface, ChannelContextAwareInterface
{
    use ChannelContextTrait;

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

    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        $locale = $submissionEvent->getLocale() ?? $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();
        $formRuntimeData = $submissionEvent->getFormRuntimeData();

        $localizedChannelConfiguration = $this->validateOutputConfig($channelConfiguration, $locale);

        $context = [
            'locale'             => $locale,
            'doubleOptInSession' => $submissionEvent->getDoubleOptInSession(),
            'channelContext'     => $this->getChannelContext(),
        ];

        $this->channelWorker->process($form, $localizedChannelConfiguration, $formRuntimeData, $workflowName, $context);
    }

    /**
     * @throws \Exception
     */
    protected function validateOutputConfig(array $channelConfiguration, string $locale): array
    {
        $localizedChannelConfiguration = $this->localeDataMapper->mapMultiDimensional($locale, 'mailTemplate', true, $channelConfiguration);

        $message = null;
        if (!isset($localizedChannelConfiguration['mailTemplate'])) {
            $message = 'No mail template definition available.';
        } elseif ($localizedChannelConfiguration['mailTemplate']['id'] === null) {
            $message = 'No mail template id available.';
        }

        if ($message === null) {
            return $localizedChannelConfiguration;
        }

        throw new \Exception($message);
    }
}
