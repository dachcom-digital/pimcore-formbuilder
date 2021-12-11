<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Event\OutputWorkflow\ChannelSubjectGuardEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Registry\ApiProviderRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

class ApiOutputChannelWorker
{
    /**
     * @var FormValuesOutputApplierInterface
     */
    protected $formValuesOutputApplier;

    /**
     * @var ApiProviderRegistry
     */
    protected $apiProviderRegistry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param FormValuesOutputApplierInterface $formValuesOutputApplier
     * @param ApiProviderRegistry              $apiProviderRegistry
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        FormValuesOutputApplierInterface $formValuesOutputApplier,
        ApiProviderRegistry $apiProviderRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->formValuesOutputApplier = $formValuesOutputApplier;
        $this->apiProviderRegistry = $apiProviderRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration)
    {
        $formRuntimeData = $submissionEvent->getFormRuntimeData();
        $locale = $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();

        $apiProviderName = $channelConfiguration['apiProvider'];
        $apiMappingData = $channelConfiguration['apiMappingData'];
        $providerConfiguration = $channelConfiguration['apiConfiguration'];

        // no data no gain.
        if (!is_array($apiMappingData)) {
            return;
        }

        $formData = $this->formValuesOutputApplier->applyForChannel($form, [], 'api', $locale);

        $mapping = $this->buildMapping([], $apiMappingData, $formData);
        $nodes = $this->buildApiNodes([], $mapping);

        $apiData = new ApiData($nodes, $providerConfiguration, $locale, $formRuntimeData, $form);

        if (null === $apiData = $this->dispatchGuardEvent($apiData, $form, $workflowName, $formRuntimeData)) {
            return;
        }

        $apiProvider = $this->apiProviderRegistry->get($apiProviderName);

        $apiProvider->process($apiData);
    }

    protected function buildApiNodes(array $nodes, array $mapping, bool $hasParent = false, ?string $parentType = null)
    {
        // repeater incoming. we need to map differently:
        if ($parentType === 'repeater') {
            return $this->buildRepeaterApiNodes($mapping);
        }

        foreach ($mapping as $mapRow) {
            $formField = $mapRow['formField'];
            $apiMappingFields = $mapRow['apiMapping'];
            $hasChildren = count($mapRow['children']) > 0;

            if ($formField === null) {
                continue;
            }

            $apiField = $hasParent ? $nodes : $this->findFormFieldValue($formField, $formField['type']);

            foreach ($apiMappingFields as $apiMappingField) {
                $nodes[$apiMappingField] = $this->findFormFieldValue($formField, $hasParent ? $parentType : $formField['type']);
            }

            if ($hasChildren) {
                if ($formField['type'] === 'fieldset' && count($apiMappingFields) === 0) {
                    $nodes = array_merge([], ...[$nodes, $this->buildApiNodes($apiField, $mapRow['children'], true, $formField['type'])]);
                } else {
                    foreach ($apiMappingFields as $apiMappingField) {
                        $nodes[$apiMappingField] = $this->buildApiNodes($apiField, $mapRow['children'], true, $formField['type']);
                    }
                }
            }
        }

        return $nodes;
    }

    protected function buildRepeaterApiNodes(array $mapping)
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
            $repeaterBlocks[] = $this->buildApiNodes([], $this->buildRepeaterBlock($mapping, $referenceFieldIndex), true, 'repeater_block');
        }

        return $repeaterBlocks;
    }

    protected function buildRepeaterBlock(array $rows, int $index)
    {
        $fields = [];
        foreach ($rows as $mapRow) {

            $repeaterFormFields = $mapRow['formField'];
            $repeaterApiMapping = $mapRow['apiMapping'];

            $fields[] = [
                'formField'  => $repeaterFormFields[$index] ?? null,
                'apiMapping' => $repeaterApiMapping,
                'children'   => []
            ];
        }

        return $fields;
    }

    protected function buildMapping(array $apiStructure, array $apiMappingData, array $formData, bool $hasParent = false)
    {
        foreach ($apiMappingData as $apiMappingField) {

            $fieldName = $apiMappingField['name'];
            $hasChildren = isset($apiMappingField['children']) && is_array($apiMappingField['children']) && count($apiMappingField['children']) > 0;
            $mapping = $apiMappingField['config']['apiMapping'] ?? null;

            $relatedFormField = $this->findFormDataField($fieldName, $formData);

            $apiField = [
                'formField'  => $relatedFormField,
                'apiMapping' => $mapping,
                'children'   => []
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

    protected function findFormFieldValue(array $node, string $type)
    {
        if (isset($node['value'])) {
            return $node['value'];
        }

        $values = [];
        foreach ($node as $blockNode) {
            if (isset($blockNode['value'])) {
                $values[] = $blockNode['value'];
            }
        }

        // we don't have more than one value in fieldset context
        if ($type === 'fieldset' && count($values) > 0) {
            return $values[0];
        }

        return $values;
    }

    protected function findFormDataField(string $requestedFieldName, array $data)
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
     * @param mixed         $subject
     * @param FormInterface $form
     * @param string        $workflowName
     * @param array         $formRuntimeData
     *
     * @return mixed|null
     *
     * @throws GuardChannelException
     * @throws GuardOutputWorkflowException
     */
    protected function dispatchGuardEvent($subject, FormInterface $form, string $workflowName, array $formRuntimeData)
    {
        $channelSubjectGuardEvent = new ChannelSubjectGuardEvent($form->getData(), $subject, $workflowName, 'api', $formRuntimeData);
        $this->eventDispatcher->dispatch(FormBuilderEvents::OUTPUT_WORKFLOW_GUARD_SUBJECT_PRE_DISPATCH, $channelSubjectGuardEvent);

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
