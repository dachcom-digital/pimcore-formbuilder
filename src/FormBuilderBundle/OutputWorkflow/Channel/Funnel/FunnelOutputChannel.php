<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\FunnelChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\FunnelAwareChannelInterface;
use FormBuilderBundle\Tool\LocaleDataMapper;

class FunnelOutputChannel implements ChannelInterface, FunnelAwareChannelInterface
{
    protected FunnelOutputChannelWorker $channelWorker;
    protected LocaleDataMapper $localeDataMapper;

    public function __construct(FunnelOutputChannelWorker $channelWorker, LocaleDataMapper $localeDataMapper)
    {
        $this->channelWorker = $channelWorker;
        $this->localeDataMapper = $localeDataMapper;
    }

    public function getFormType(): string
    {
        return FunnelChannelType::class;
    }

    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    public function getUsedFormFieldNames(array $channelConfiguration): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {

    }
}
