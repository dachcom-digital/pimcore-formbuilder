<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ApiChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class ApiOutputChannel implements ChannelInterface
{
    protected ApiOutputChannelWorker $apiOutputChannelWorker;

    public function __construct(ApiOutputChannelWorker $apiOutputChannelWorker)
    {
        $this->apiOutputChannelWorker = $apiOutputChannelWorker;
    }

    public function getFormType(): string
    {
        return ApiChannelType::class;
    }

    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    public function getUsedFormFieldNames(array $channelConfiguration): array
    {
        if (count($channelConfiguration['apiMappingData']) === 0) {
            return [];
        }

        return $this->findUsedFormFieldsInConfiguration($channelConfiguration['apiMappingData']);
    }

    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        $this->apiOutputChannelWorker->process($submissionEvent, $workflowName, $channelConfiguration);
    }

    protected function findUsedFormFieldsInConfiguration(array $definitionFields, array $fieldNames = []): array
    {
        foreach ($definitionFields as $definitionField) {
            $hasChildren = isset($definitionField['children']) && is_array($definitionField['children']) && count($definitionField['children']) > 0;
            $hasApiFields = isset($definitionField['config']['apiMapping']) && is_array($definitionField['config']['apiMapping']) && count($definitionField['config']['apiMapping']) > 0;

            if ($hasApiFields) {
                $fieldNames[] = $definitionField['name'];
            }

            if ($hasChildren === true) {
                $fieldNames = $this->findUsedFormFieldsInConfiguration($definitionField['children'], $fieldNames);
            }
        }

        return $fieldNames;
    }
}
