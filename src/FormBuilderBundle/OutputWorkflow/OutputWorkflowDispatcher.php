<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;

class OutputWorkflowDispatcher implements OutputWorkflowDispatcherInterface
{
    /**
     * @var OutputWorkflowChannelRegistry
     */
    protected $channelRegistry;

    /**
     * @param OutputWorkflowChannelRegistry $channelRegistry
     */
    public function __construct(OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->channelRegistry = $channelRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent)
    {
        foreach ($outputWorkflow->getChannels() as $index => $channel) {
            try {
                $channelProcessor = $this->channelRegistry->get($channel->getType());
                $channelProcessor->dispatchOutputProcessing($submissionEvent, $outputWorkflow->getName(), $channel->getConfiguration());
            } catch (\Throwable $e) {
                throw new \Exception(
                    sprintf('"%s" workflow channel "%s" errored at step %d: %s',
                        $outputWorkflow->getName(),
                        $channel->getType(),
                        $index+1,
                        $e->getMessage()
                    )
                );
            }
        }
    }
}