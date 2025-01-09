<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ApiChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelContextAwareInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Trait\ChannelContextTrait;

class ApiOutputChannel implements ChannelInterface, ChannelContextAwareInterface
{
    use ChannelContextTrait;

    public function __construct(protected ApiOutputChannelWorker $apiOutputChannelWorker)
    {
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
        $context = [
            'channelContext' => $this->getChannelContext(),
        ];

        $this->apiOutputChannelWorker->process($submissionEvent, $workflowName, $channelConfiguration, $context);
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
