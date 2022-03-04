<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use FormBuilderBundle\Exception\OutputWorkflow;
use Symfony\Contracts\EventDispatcher\Event;

class OutputWorkflowSignalsEvent extends Event
{
    protected array $signals;
    protected ?\Throwable $exception;

    public function __construct(array $signals, ?\Throwable $exception)
    {
        $this->signals = $signals;
        $this->exception = $exception;
    }

    public function hasException(): bool
    {
        return $this->exception instanceof \Throwable;
    }

    public function hasGuardException(): bool
    {
        return $this->exception instanceof OutputWorkflow\GuardChannelException ||
            $this->exception instanceof OutputWorkflow\GuardOutputWorkflowException ||
            $this->exception instanceof OutputWorkflow\GuardStackedException;
    }

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    /**
     * @return array<int, OutputWorkflowSignalEvent>
     */
    public function getAllSignals(): array
    {
        return $this->signals;
    }

    /**
     * @return array<int, OutputWorkflowSignalEvent>
     */
    public function getSignalsByName(string $name): array
    {
        return array_values(
            array_filter($this->signals, static function (OutputWorkflowSignalEvent $signal) use ($name) {
                return $signal->getName() === $name;
            })
        );
    }
}
