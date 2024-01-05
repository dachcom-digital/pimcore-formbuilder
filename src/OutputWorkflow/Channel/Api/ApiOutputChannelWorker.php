<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Registry\ApiProviderRegistry;
use FormBuilderBundle\Registry\FieldTransformerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

class ApiOutputChannelWorker
{
    public function __construct(
        protected FormValuesOutputApplierInterface $formValuesOutputApplier,
        protected ApiProviderRegistry $apiProviderRegistry,
        protected FieldTransformerRegistry $fieldTransformerRegistry,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function process(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        $formRuntimeData = $submissionEvent->getFormRuntimeData();
        $locale = $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();

        $apiProviderName = $channelConfiguration['apiProvider'];
        $apiMappingData = $channelConfiguration['apiMappingData'];
        $providerConfiguration = $channelConfiguration['apiConfiguration'];

        // no data, no gain.
        if (!is_array($apiMappingData)) {
            return;
        }

        $formData = $this->formValuesOutputApplier->applyForChannel($form, [], 'api', $locale);

        $mapping = $this->buildMapping([], $apiMappingData, $formData);
        $nodes = $this->buildApiNodes([], $formData, $mapping);

        try {
            $apiProvider = $this->apiProviderRegistry->get($apiProviderName);
        } catch (\Throwable $e) {
            // no api provider found. return silently.
            return;
        }

        $apiData = new ApiData($apiProviderName, $nodes, $providerConfiguration, $locale, $formRuntimeData, $form);

        if (null === $apiData = $this->dispatchGuardEvent($apiData, $form, $workflowName, $formRuntimeData)) {
            return;
        }

        $apiProvider->process($apiData);
    }

    protected function buildMapping(array $apiStructure, array $apiMappingData, array $formData, bool $hasParent = false): array
    {
        foreach ($apiMappingData as $apiMappingField) {

            $fieldName = $apiMappingField['name'];
            $hasChildren = isset($apiMappingField['children']) && is_array($apiMappingField['children']) && count($apiMappingField['children']) > 0;
            $mapping = $apiMappingField['config']['apiMapping'] ?? null;
            $fieldTransformer = $apiMappingField['config']['fieldTransformer'] ?? null;

            $apiField = [
                'formField'        => $this->findFormDataField($fieldName, $formData),
                'apiMapping'       => $mapping,
                'fieldTransformer' => $fieldTransformer,
                'children'         => []
            ];

            if ($hasParent === true) {
                $apiStructure['children'][] = $apiField;
            }

            if ($hasChildren) {
                $apiStructure[] = $this->buildMapping($apiField, $apiMappingField['children'], $formData, true);
                continue;
            }

            if ($hasParent === false) {
                $apiStructure[] = $apiField;
            }

        }

        return $apiStructure;
    }

    protected function buildApiNodes(array $nodes, array $formData, array $mapping, bool $hasParent = false, ?string $parentType = null): array
    {
        // repeater incoming. we need to map differently:
        if ($parentType === 'repeater') {
            return $this->buildRepeaterApiNodes($mapping, $formData);
        }

        foreach ($mapping as $mapRow) {
            $formField = $mapRow['formField'];
            $apiMappingFields = $mapRow['apiMapping'];
            $fieldTransformer = $mapRow['fieldTransformer'];
            $hasChildren = count($mapRow['children']) > 0;

            if ($formField === null) {
                continue;
            }

            foreach ($apiMappingFields as $apiMappingField) {

                $context = [
                    'type'            => $formField['type'] ?? null,
                    'parentType'      => $hasParent ? $parentType : null,
                    'formData'        => $formData,
                    'formField'       => $formField,
                    'apiMappingField' => $apiMappingField,
                ];

                $nodes[$apiMappingField] = $this->findFormFieldValue($formField, $fieldTransformer, $context);

                unset($apiMappingField);
            }

            if ($hasChildren) {
                $apiField = $hasParent ? $nodes : [];
                if ($formField['type'] === 'fieldset' && count($apiMappingFields) === 0) {
                    $nodes = array_merge([], ...[$nodes, $this->buildApiNodes($apiField, $formData, $mapRow['children'], true, $formField['type'])]);
                } else {
                    foreach ($apiMappingFields as $apiMappingField) {
                        $nodes[$apiMappingField] = $this->buildApiNodes($apiField, $formData, $mapRow['children'], true, $formField['type']);
                    }
                }
            }
        }

        return $nodes;
    }

    protected function buildRepeaterApiNodes(array $mapping, array $formData): array
    {
        if (count($mapping) === 0) {
            return [];
        }

        $referenceRow = $mapping[0];
        if (!is_array($referenceRow['formField']) || count($referenceRow['formField']) === 0) {
            return [];
        }

        $repeaterBlocks = [];
        foreach ($referenceRow['formField'] as $referenceFieldIndex => $reference) {
            $repeaterBlocks[] = $this->buildApiNodes([], $formData, $this->buildRepeaterBlock($mapping, $referenceFieldIndex), true, 'repeater_block');
        }

        return $repeaterBlocks;
    }

    protected function buildRepeaterBlock(array $rows, int $index): array
    {
        $fields = [];
        foreach ($rows as $mapRow) {

            $repeaterFormFields = $mapRow['formField'];
            $repeaterApiMapping = $mapRow['apiMapping'];
            $fieldTransformer = $mapRow['fieldTransformer'];

            $fields[] = [
                'formField'        => $repeaterFormFields[$index] ?? null,
                'apiMapping'       => $repeaterApiMapping,
                'fieldTransformer' => $fieldTransformer,
                'children'         => []
            ];
        }

        return $fields;
    }

    protected function findFormFieldValue(array $node, ?string $fieldTransformer, array $context): mixed
    {
        $type = $context['type'] ?? $context['parentType'];

        if (isset($node['value'])) {
            return $this->applyFieldTransformer($node['value'], $fieldTransformer, $context);
        }

        $values = [];
        foreach ($node as $blockNode) {
            if (isset($blockNode['value'])) {
                $values[] = $blockNode['value'];
            }
        }

        // we don't have more than one value in fieldset context
        if ($type === 'fieldset' && count($values) > 0) {
            return $this->applyFieldTransformer($values[0], $fieldTransformer, $context);
        }

        return $this->applyFieldTransformer($values, $fieldTransformer, $context);
    }

    protected function applyFieldTransformer($value, ?string $fieldTransformerName, array $context): mixed
    {
        if ($fieldTransformerName === null) {
            return $value;
        }

        try {
            $fieldTransformer = $this->fieldTransformerRegistry->get($fieldTransformerName);
        } catch (\Throwable $e) {
            return $value;
        }

        return $fieldTransformer->transform($value, $context);
    }

    protected function findFormDataField(string $requestedFieldName, array $data): ?array
    {
        foreach ($data as $fieldData) {

            if ($requestedFieldName === $fieldData['name']) {
                return $fieldData;
            }

            if (!isset($fieldData['fields'])) {
                continue;
            }

            $fieldBlockData = [];
            foreach ($fieldData['fields'] as $fieldBlock) {
                if (null !== $blockData = $this->findFormDataField($requestedFieldName, $fieldBlock)) {
                    $fieldBlockData[] = $blockData;
                }
            }

            if (count($fieldBlockData) > 0) {
                return $fieldBlockData;
            }
        }

        return null;
    }

    /**
     * @throws GuardChannelException
     * @throws GuardOutputWorkflowException
     */
    protected function dispatchGuardEvent($subject, FormInterface $form, string $workflowName, array $formRuntimeData): mixed
    {
        $channelSubjectGuardEvent = new ChannelSubjectGuardEvent($form->getData(), $subject, $workflowName, 'api', $formRuntimeData);
        $this->eventDispatcher->dispatch($channelSubjectGuardEvent, FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH);

        if ($channelSubjectGuardEvent->isSuspended()) {
            return null;
        }

        if ($channelSubjectGuardEvent->shouldStopChannel()) {
            throw new GuardChannelException($channelSubjectGuardEvent->getFailMessage());
        } elseif ($channelSubjectGuardEvent->shouldStopOutputWorkflow()) {
            throw new GuardOutputWorkflowException($channelSubjectGuardEvent->getFailMessage());
        }

        return $channelSubjectGuardEvent->getSubject();
    }
}
