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
        $locale = $submissionEvent->getLocale() ?? $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();
        $formRuntimeData = $submissionEvent->getFormRuntimeData();

        $context = [
            'locale'             => $locale,
            'doubleOptInSession' => $submissionEvent->getDoubleOptInSession(),
            'channelContext'     => $this->getChannelContext(),
        ];

        $this->apiOutputChannelWorker->process($form, $channelConfiguration, $formRuntimeData, $workflowName, $context);
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
