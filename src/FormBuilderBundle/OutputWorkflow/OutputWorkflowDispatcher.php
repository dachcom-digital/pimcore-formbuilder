<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalsEvent;
use FormBuilderBundle\EventSubscriber\OutputWorkflowSignalSubscriber;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardStackedException;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OutputWorkflowDispatcher implements OutputWorkflowDispatcherInterface
{
    protected EventDispatcherInterface $eventDispatcher;
    protected OutputWorkflowChannelRegistry $channelRegistry;
    protected OutputWorkflowSignalSubscriber $subscriber;

    public function __construct(EventDispatcherInterface $eventDispatcher, OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->channelRegistry = $channelRegistry;
    }

    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void
    {
        $this->subscriber = new OutputWorkflowSignalSubscriber();
        $this->eventDispatcher->addSubscriber($this->subscriber);

        $exceptionStack = [];
        foreach ($outputWorkflow->getChannels() as $index => $channel) {
            try {
                $channelProcessor = $this->channelRegistry->get($channel->getType());
                $channelProcessor->dispatchOutputProcessing($submissionEvent, $outputWorkflow->getName(), $channel->getConfiguration());
            } catch (GuardChannelException $e) {
                $exceptionStack[] = $e;
            } catch (GuardOutputWorkflowException $e) {

                $this->dispatchSignalsEvent($e);

                throw $e;

            } catch (\Throwable $e) {

                $this->dispatchSignalsEvent($e);

                throw new \Exception(
                    sprintf(
                        '"%s" workflow channel "%s" errored at step %d: %s',
                        $outputWorkflow->getName(),
                        $channel->getType(),
                        $index + 1,
                        $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
                    )
                );
            }
        }

        if (count($exceptionStack) > 0) {

            $exception = new GuardStackedException($exceptionStack);
            $this->dispatchSignalsEvent($exception);

            throw $exception;
        }

        $this->dispatchSignalsEvent();
    }

    protected function dispatchSignalsEvent(?\Exception $exception = null): void
    {
        $signals = $this->subscriber->getSignals();
        $this->eventDispatcher->removeSubscriber($this->subscriber);
        $this->eventDispatcher->dispatch(new OutputWorkflowSignalsEvent($signals, $exception), FormBuilderEvents::OUTPUT_WORKFLOW_SIGNALS);
    }
}
