<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\EventSubscriber\SignalSubscribeHandler;
use FormBuilderBundle\Exception\OutputWorkflow\GuardChannelException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardStackedException;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Registry\OutputWorkflowChannelRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OutputWorkflowDispatcher implements OutputWorkflowDispatcherInterface
{
    protected OutputWorkflowChannelRegistry $channelRegistry;
    protected FunnelWorkerInterface $funnelWorker;
    protected SignalSubscribeHandler $signalSubscribeHandler;

    public function __construct(
        OutputWorkflowChannelRegistry $channelRegistry,
        FunnelWorkerInterface $funnelWorker,
        SignalSubscribeHandler $signalSubscribeHandler
    ) {
        $this->channelRegistry = $channelRegistry;
        $this->funnelWorker = $funnelWorker;
        $this->signalSubscribeHandler = $signalSubscribeHandler;
    }

    public function dispatch(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void
    {
        if ($outputWorkflow->isFunnelWorkflow()) {
            $this->dispatchOutputWorkflowFunnelInitiating($outputWorkflow, $submissionEvent);
        } else {
            $this->dispatchOutputWorkflowInitiating($outputWorkflow, $submissionEvent);
        }
    }

    protected function dispatchOutputWorkflowInitiating(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void
    {
        $this->signalSubscribeHandler->listen(SignalSubscribeHandler::CHANNEL_OUTPUT_WORKFLOW);

        $exceptionStack = [];
        foreach ($outputWorkflow->getChannels() as $index => $channel) {
            try {
                $channelProcessor = $this->channelRegistry->get($channel->getType());
                $channelProcessor->dispatchOutputProcessing($submissionEvent, $outputWorkflow->getName(), $channel->getConfiguration());
            } catch (GuardChannelException $e) {
                $exceptionStack[] = $e;
            } catch (GuardOutputWorkflowException $e) {

                $this->signalSubscribeHandler->broadcast(['exception' => $e]);

                throw $e;

            } catch (\Throwable $e) {

                $this->signalSubscribeHandler->broadcast(['exception' => $e]);

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
            $this->signalSubscribeHandler->broadcast(['exception' => $exception]);

            throw $exception;
        }

        $this->signalSubscribeHandler->broadcast();
    }

    protected function dispatchOutputWorkflowFunnelInitiating(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void
    {
        $this->signalSubscribeHandler->listen(SignalSubscribeHandler::CHANNEL_FUNNEL_INITIATE);

        try {
            $this->funnelWorker->initiateFunnel($outputWorkflow, $submissionEvent);
        } catch (\Throwable $e) {

            $this->signalSubscribeHandler->broadcast(['exception' => $e]);

            throw new \Exception(
                sprintf(
                    '"%s" workflow funnel errored at initialization state: %s',
                    $outputWorkflow->getName(),
                    $e->getMessage()
                )
            );
        }

        $this->signalSubscribeHandler->broadcast();
    }

    public function dispatchOutputWorkflowFunnelProcessing(OutputWorkflowInterface $outputWorkflow, Request $request): Response
    {
        // signals for funnel processing will be handled in FunnelRouteListener

        try {
            $response = $this->funnelWorker->processFunnel($outputWorkflow, $request);
        } catch (\Throwable $e) {

            throw new \Exception(
                sprintf(
                    '"%s" workflow funnel errored at processing state: %s',
                    $outputWorkflow->getName(),
                    $e->getMessage()
                )
            );
        }

        return $response;
    }

}
