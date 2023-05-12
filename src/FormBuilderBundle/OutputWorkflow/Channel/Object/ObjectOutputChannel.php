<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Factory\ObjectResolverFactoryInterface;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ObjectChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;
use Pimcore\Model\DataObject;

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

    /**
     * @deprecated since 4.5.0 and will be removed in 5.0.0. Update all forms before migrating
     */
    public static function parseLegacyDynamicObjectResolver(array $configuration): array
    {
        if (!array_key_exists('dynamicObjectResolver', $configuration)) {
            return $configuration;
        }

        // already migrated
        if (array_key_exists('dynamicObjectResolverClass', $configuration)) {
            return $configuration;
        }

        $dynamicObjectResolverClass = null;
        if (!empty($configuration['dynamicObjectResolver'])) {
            $resolvingObject = $configuration['resolvingObject'] ?? null;
            if (is_array($resolvingObject)) {
                $object = DataObject::getById($resolvingObject['id']);
                $dynamicObjectResolverClass = $object instanceof DataObject\Concrete ? $object->getClassName() : null;
            }
        }

        if ($dynamicObjectResolverClass === null) {
            unset($configuration['dynamicObjectResolver']);

            return $configuration;
        }

        $configuration['dynamicObjectResolverClass'] = $dynamicObjectResolverClass;
        unset($configuration['resolvingObject']);

        return $configuration;
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

        $channelConfiguration = self::parseLegacyDynamicObjectResolver($channelConfiguration);

        $objectMappingData = $channelConfiguration['objectMappingData'];

        $dynamicObjectResolverActive = false;
        if (array_key_exists('dynamicObjectResolver', $channelConfiguration)) {
            $dynamicObjectResolverActive = true;
        }

        if ($channelConfiguration['resolveStrategy'] === 'newObject') {
            $objectResolver = $this->objectResolverFactory->createForNewObject($objectMappingData);
            $objectResolver->setResolvingObjectClass($dynamicObjectResolverActive ? null : $channelConfiguration['resolvingObjectClass']);
            $objectResolver->setStoragePath($dynamicObjectResolverActive ? [] : $channelConfiguration['storagePath']);
        } elseif ($channelConfiguration['resolveStrategy'] === 'existingObject') {
            $objectResolver = $this->objectResolverFactory->createForExistingObject($objectMappingData);
            $objectResolver->setResolvingObject($dynamicObjectResolverActive ? [] : $channelConfiguration['resolvingObject']);
        } else {
            throw new \Exception(sprintf('no object resolver for strategy "%s" found.', $channelConfiguration['resolveStrategy']));
        }

        if (array_key_exists('dynamicObjectResolver', $channelConfiguration)) {
            $objectResolver->setDynamicObjectResolver($channelConfiguration['dynamicObjectResolver'], $channelConfiguration['dynamicObjectResolverClass']);
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
