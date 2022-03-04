<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use FormBuilderBundle\Exception\OutputWorkflow;
use Symfony\Component\EventDispatcher\Event;

class OutputWorkflowSignalsEvent extends Event
{
    /**
     * @var array
     */
    protected $signals;

    /**
     * @var \Throwable|null
     */
    protected $exception;

    /**
     * @param array           $signals
     * @param \Throwable|null $exception
     */
    public function __construct(array $signals, $exception)
    {
        $this->signals = $signals;
        $this->exception = $exception;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->exception instanceof \Throwable;
    }

    /**
     * @return bool
     */
    public function hasGuardException(): bool
    {
        return $this->exception instanceof OutputWorkflow\GuardChannelException ||
            $this->exception instanceof OutputWorkflow\GuardOutputWorkflowException ||
            $this->exception instanceof OutputWorkflow\GuardStackedException;
    }

    /**
     * @return \Throwable|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return OutputWorkflowSignalEvent[]
     */
    public function getAllSignals()
    {
        return $this->signals;
    }

    /**
     * @return OutputWorkflowSignalEvent[]
     */
    public function getSignalsByName(string $name)
    {
        return array_values(
            array_filter($this->signals, static function (OutputWorkflowSignalEvent $signal) use ($name) {
                return $signal->getName() === $name;
            })
        );
    }
}
