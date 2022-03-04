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
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var OutputWorkflowChannelRegistry
     */
    protected $channelRegistry;

    /**
     * @var OutputWorkflowSignalSubscriber
     */
    protected $subscriber;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param OutputWorkflowChannelRegistry $channelRegistry
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, OutputWorkflowChannelRegistry $channelRegistry)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->channelRegistry = $channelRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent)
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
                        $e->getMessage()
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

    /**
     * @param \Throwable|null $exception
     */
    protected function dispatchSignalsEvent($exception = null): void
    {
        $signals = $this->subscriber->getSignals();
        $this->eventDispatcher->removeSubscriber($this->subscriber);
        $this->eventDispatcher->dispatch(FormBuilderEvents::OUTPUT_WORKFLOW_SIGNALS, new OutputWorkflowSignalsEvent($signals, $exception));
    }
}
