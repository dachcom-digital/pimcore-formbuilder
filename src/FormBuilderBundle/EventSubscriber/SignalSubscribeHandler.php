<?php

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalsEvent;
use FormBuilderBundle\EventSubscriber\SignalStorage\ArraySignalStorage;
use FormBuilderBundle\EventSubscriber\SignalStorage\ProviderAwareStorageInterface;
use FormBuilderBundle\EventSubscriber\SignalStorage\SignalStorageInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Registry\StorageProviderRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SignalSubscribeHandler implements EventSubscriberInterface
{
    public const CHANNEL_FUNNEL_INITIATE = 'funnel.initiate';
    public const CHANNEL_FUNNEL_PROCESS = 'funnel.process';
    public const CHANNEL_OUTPUT_WORKFLOW = 'output_workflow';

    protected Configuration $configuration;
    protected StorageProviderRegistry $storageProviderRegistry;
    protected EventDispatcherInterface $eventDispatcher;
    protected SignalStorageInterface $signalStorage;

    protected ?string $channel = null;

    public function __construct(
        Configuration $configuration,
        StorageProviderRegistry $storageProviderRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->storageProviderRegistry = $storageProviderRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OutputWorkflowSignalEvent::NAME => 'addSignal',
        ];
    }

    public function addSignal(OutputWorkflowSignalEvent $signalEvent): void
    {
        if ($this->channel === null) {
            throw new \Exception('Cannot add signal, no channel has been defined.');
        }

        $this->signalStorage->storeSignal($signalEvent);
    }

    public function listen(string $channel, array $context = []): void
    {
        if ($this->channel !== null) {
            return;
        }

        $this->channel = $channel;
        $this->signalStorage = $this->buildSignalStorage($channel, $context);
        $this->eventDispatcher->addSubscriber($this);
    }

    public function broadcast(array $context = []): void
    {
        if ($this->channel === null) {
            return;
        }

        $this->channel = null;
        $this->eventDispatcher->removeSubscriber($this);

        $this->eventDispatcher->dispatch(
            new OutputWorkflowSignalsEvent(
                $this->signalStorage->getSignals(),
                $context['exception'] ?? null
            ),
            FormBuilderEvents::OUTPUT_WORKFLOW_SIGNALS
        );
    }

    private function buildSignalStorage(string $channel, array $context): SignalStorageInterface
    {
        if ($channel === self::CHANNEL_OUTPUT_WORKFLOW) {
            return new ArraySignalStorage();
        }

        if ($channel === self::CHANNEL_FUNNEL_INITIATE) {
            return new ArraySignalStorage();
        }

        if ($channel === self::CHANNEL_FUNNEL_PROCESS) {

            $funnelConfiguration = $this->configuration->getConfig('funnel');

            $signalStorageClass = $funnelConfiguration['signal_storage_class'];
            $signalStorageProvider = $funnelConfiguration['storage_provider'];

            /** @var SignalStorageInterface $signalStorageClass */
            $signalStorage = new $signalStorageClass($context);

            if ($signalStorage instanceof ProviderAwareStorageInterface) {
                $signalStorage->setStorageProvider($this->storageProviderRegistry->get($signalStorageProvider));
            }

            return $signalStorage;
        }

        throw new \Exception(sprintf('Cannot determinate signal storage for channel "%s"', $channel));
    }
}
