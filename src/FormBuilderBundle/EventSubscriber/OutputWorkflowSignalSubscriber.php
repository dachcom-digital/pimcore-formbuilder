<?php declare(strict_types=1);

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OutputWorkflowSignalSubscriber implements EventSubscriberInterface
{
    protected array $signals = [];

    public static function getSubscribedEvents(): array
    {
        return [
            OutputWorkflowSignalEvent::NAME => 'addSignal',
        ];
    }

    public function addSignal(OutputWorkflowSignalEvent $signalEvent): void
    {
        $this->signals[] = $signalEvent;
    }

    public function getSignals(): array
    {
        return $this->signals;
    }
}