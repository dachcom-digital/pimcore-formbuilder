<?php

namespace FormBuilderBundle\EventSubscriber\SignalStorage;

use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;

class ArraySignalStorage implements SignalStorageInterface
{
    protected array $context;
    protected array $signals = [];

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    public function storeSignal(OutputWorkflowSignalEvent $signal): void
    {
        $this->signals[] = $signal;
    }

    public function getSignals(): array
    {
        return $this->signals;
    }
}
