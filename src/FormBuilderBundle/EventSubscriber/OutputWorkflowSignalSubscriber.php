<?php declare(strict_types=1);

namespace FormBuilderBundle\EventSubscriber;

use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OutputWorkflowSignalSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $signals = [];

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            OutputWorkflowSignalEvent::NAME => 'addSignal',
        ];
    }

    /**
     * @param OutputWorkflowSignalEvent $signalEvent
     */
    public function addSignal(OutputWorkflowSignalEvent $signalEvent)
    {
        $this->signals[] = $signalEvent;
    }

    /**
     * @return array
     */
    public function getSignals()
    {
        return $this->signals;
    }
}