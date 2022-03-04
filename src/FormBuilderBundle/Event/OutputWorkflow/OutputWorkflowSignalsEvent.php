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
     * @var \Exception|null
     */
    protected $exception;

    /**
     * @param array           $signals
     * @param \Exception|null $exception
     */
    public function __construct(array $signals, ?\Exception $exception)
    {
        $this->signals = $signals;
        $this->exception = $exception;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->exception instanceof \Exception;
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
     * @return \Exception|null
     */
    public function getException(): ?\Exception
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
