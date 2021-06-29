<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Factory\ObjectResolverFactoryInterface;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ObjectChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class ObjectOutputChannel implements ChannelInterface
{
    protected ObjectResolverFactoryInterface $objectResolverFactory;

    public function __construct(ObjectResolverFactoryInterface $objectResolverFactory)
    {
        $this->objectResolverFactory = $objectResolverFactory;
    }

    public function getFormType(): string
    {
        return ObjectChannelType::class;
    }

    public function isLocalizedConfiguration(): bool
    {
        return false;
    }

    public function getUsedFormFieldNames(array $channelConfiguration): array
    {
        if (count($channelConfiguration['objectMappingData']) === 0) {
            return [];
        }

        return $this->findUsedFormFieldsInConfiguration($channelConfiguration['objectMappingData']);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration): void
    {
        $formRuntimeData = $submissionEvent->getFormRuntimeData();
        $locale = $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();

        $objectMappingData = $channelConfiguration['objectMappingData'];

        if ($channelConfiguration['resolveStrategy'] === 'newObject') {
            $objectResolver = $this->objectResolverFactory->createForNewObject($objectMappingData);
            $objectResolver->setResolvingObjectClass($channelConfiguration['resolvingObjectClass']);
            $objectResolver->setStoragePath($channelConfiguration['storagePath']);
        } elseif ($channelConfiguration['resolveStrategy'] === 'existingObject') {
            $objectResolver = $this->objectResolverFactory->createForExistingObject($objectMappingData);
            $objectResolver->setResolvingObject($channelConfiguration['resolvingObject']);
            $objectResolver->setDynamicObjectResolver($channelConfiguration['dynamicObjectResolver']);
        } else {
            throw new \Exception(sprintf('no object resolver for strategy "%s" found.', $channelConfiguration['resolveStrategy']));
        }

        $objectResolver->setForm($form);
        $objectResolver->setLocale($locale);
        $objectResolver->setWorkflowName($workflowName);
        $objectResolver->setFormRuntimeData($formRuntimeData);

        $objectResolver->resolve();
    }

    protected function findUsedFormFieldsInConfiguration(array $definitionFields, array $fieldNames = []): array
    {
        foreach ($definitionFields as $definitionField) {
            $hasChildren = isset($definitionField['childs']) && is_array($definitionField['childs']) && count($definitionField['childs']) > 0;
            $hasWorkerFieldMapping = isset($definitionField['config']['workerData']) && isset($definitionField['config']['workerData']['fieldMapping']);

            if ($definitionField['type'] === 'form_field' && $hasChildren) {
                $fieldNames[] = $definitionField['config']['name'];
            }

            if ($hasChildren === true) {
                $fieldNames = $this->findUsedFormFieldsInConfiguration($definitionField['childs'], $fieldNames);
            } elseif ($hasWorkerFieldMapping === true) {
                $fieldNames = $this->findUsedFormFieldsInConfiguration($definitionField['config']['workerData']['fieldMapping'], $fieldNames);
            }
        }

        return $fieldNames;
    }
}
