<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Object;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Factory\ObjectResolverFactoryInterface;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\ObjectChannelType;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelInterface;

class ObjectOutputChannel implements ChannelInterface
{
    /**
     * @var ObjectResolverFactoryInterface
     */
    protected $objectResolverFactory;

    /**
     * @param ObjectResolverFactoryInterface $objectResolverFactory
     */
    public function __construct(ObjectResolverFactoryInterface $objectResolverFactory)
    {
        $this->objectResolverFactory = $objectResolverFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType(): string
    {
        return ObjectChannelType::class;
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
    public function dispatchOutputProcessing(SubmissionEvent $submissionEvent, string $workflowName, array $channelConfiguration)
    {
        $formConfiguration = $submissionEvent->getFormConfiguration();
        $locale = $submissionEvent->getRequest()->getLocale();
        $form = $submissionEvent->getForm();

        $storagePath = $channelConfiguration['storagePath'];
        $objectMappingData = $channelConfiguration['objectMappingData'];

        if ($channelConfiguration['resolveStrategy'] === 'newObject') {
            $objectResolver = $this->objectResolverFactory->createForNewObject($storagePath, $objectMappingData);
            $objectResolver->setResolvingObjectClass($channelConfiguration['resolvingObjectClass']);
        } elseif ($channelConfiguration['resolveStrategy'] === 'existingObject') {
            $objectResolver = $this->objectResolverFactory->createForExistingObject($storagePath, $objectMappingData);
            $objectResolver->setResolvingObject($channelConfiguration['resolvingObject']);
        } else {
            throw new \Exception(sprintf('no object resolver for strategy "%s" found.', $channelConfiguration['resolveStrategy']));
        }

        $objectResolver->setForm($form);
        $objectResolver->setLocale($locale);
        $objectResolver->setWorkflowName($workflowName);
        $objectResolver->setFormRuntimeOptions($formConfiguration['form_runtime_options']);

        $objectResolver->resolve();
    }
}