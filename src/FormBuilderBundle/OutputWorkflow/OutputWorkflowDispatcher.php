<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardStackedException;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;

class OutputWorkflowDispatcher implements OutputWorkflowDispatcherInterface
{
    protected OutputWorkflowChannelRegistry $channelRegistry;

    public function __construct(OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->channelRegistry = $channelRegistry;
    }

    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void
    {
        $exceptionStack = [];
        foreach ($outputWorkflow->getChannels() as $index => $channel) {
            try {
                $channelProcessor = $this->channelRegistry->get($channel->getType());
                $channelProcessor->dispatchOutputProcessing($submissionEvent, $outputWorkflow->getName(), $channel->getConfiguration());
            } catch (GuardChannelException $e) {
                $exceptionStack[] = $e;
            } catch (GuardOutputWorkflowException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new \Exception(
                    sprintf(
                        '"%s" workflow channel "%s" errored at step %d: %s',
                        $outputWorkflow->getName(),
                        $channel->getType(),
                        $index + 1,
                        $e->getMessage()
                    )
                );
            }
        }

        if (count($exceptionStack) > 0) {
            throw new GuardStackedException($exceptionStack);
        }
    }
}
