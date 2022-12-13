<?php

namespace FormBuilderBundle\EventSubscriber\SignalStorage;

use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;

interface SignalStorageInterface
{
    public function storeSignal(OutputWorkflowSignalEvent $signal): void;

    public function getSignals(): array;
}