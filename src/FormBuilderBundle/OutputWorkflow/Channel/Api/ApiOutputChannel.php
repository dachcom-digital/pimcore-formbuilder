<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ApiChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class ApiOutputChannel implements ChannelInterface
{
    /**
     * @var ApiOutputChannelWorker
     */
    protected $apiOutputChannelWorker;

    public function __construct(ApiOutputChannelWorker $apiOutputChannelWorker)
    {
        $this->apiOutputChannelWorker = $apiOutputChannelWorker;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType(): string
    {
        return ApiChannelType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedFormFieldNames(array $channelConfiguration)
    {
        if (count($channelConfiguration['apiMappingData']) === 0) {
            return [];
        }

        return $this->findUsedFormFieldsInConfiguration($channelConfiguration['apiMappingData']);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration)
    {
        $this->apiOutputChannelWorker->process($submissionEvent, $workflowName, $channelConfiguration);
    }

    /**
     * @param array $definitionFields
     * @param array $fieldNames
     *
     * @return array
     */
    protected function findUsedFormFieldsInConfiguration(array $definitionFields, array $fieldNames = [])
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
