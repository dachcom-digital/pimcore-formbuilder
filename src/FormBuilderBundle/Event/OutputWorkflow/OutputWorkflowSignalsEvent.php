<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use FormBuilderBundle\Exception\OutputWorkflow;
use Symfony\Contracts\EventDispatcher\Event;

class OutputWorkflowSignalsEvent extends Event
{
    protected string $channel;
    protected array $signals;
    protected array $context;

    public function __construct(string $channel, array $signals, array $context)
    {
        $this->channel = $channel;
        $this->signals = $signals;
        $this->context = $context;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function hasContextItem(string $contextItem): bool
    {
        return array_key_exists($contextItem, $this->context);
    }

    public function getContextItem(string $contextItem): mixed
    {
        if (!$this->hasContextItem($contextItem)) {
            return null;
        }

        return $this->context[$contextItem];
    }

    public function hasException(): bool
    {
        return $this->hasContextItem('exception') && $this->getContextItem('exception') instanceof \Throwable;
    }

    public function hasGuardException(): bool
    {
        if (!$this->hasException()) {
            return false;
        }

        $exception = $this->getContextItem('exception');

        return $exception instanceof OutputWorkflow\GuardChannelException ||
            $exception instanceof OutputWorkflow\GuardOutputWorkflowException ||
            $exception instanceof OutputWorkflow\GuardStackedException;
    }

    public function getException(): ?\Throwable
    {
        if (!$this->hasException()) {
            return null;
        }

        return $this->getContextItem('exception');
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
