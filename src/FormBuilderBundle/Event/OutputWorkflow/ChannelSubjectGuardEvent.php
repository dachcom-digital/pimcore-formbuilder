<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use FormBuilderBundle\Form\Data\FormDataInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ChannelSubjectGuardEvent extends Event
{
    protected FormDataInterface $formData;
    protected $subject;
    protected array $formRuntimeData;
    protected string $workflowName;
    protected string $channelType;
    protected bool $suspended = false;
    protected bool $failed = false;
    protected bool $currentChannelOnly = true;
    protected ?string $failMessage = null;

    public function __construct(FormDataInterface $formData, $subject, string $workflowName, string $channelType, array $formRuntimeData)
    {
        $this->formData = $formData;
        $this->subject = $subject;
        $this->workflowName = $workflowName;
        $this->channelType = $channelType;
        $this->formRuntimeData = $formRuntimeData;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getFormData(): FormDataInterface
    {
        return $this->formData;
    }

    public function getFormRuntimeData(): array
    {
        return $this->formRuntimeData;
    }

    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    public function getChannelType(): string
    {
        return $this->channelType;
    }

    /**
     * Silently suspend current process without any notices.
     */
    public function shouldSuspend(): void
    {
        $this->suspended = true;
        $this->failed = false;
        $this->failMessage = null;
        $this->currentChannelOnly = false;
    }

    /**
     * Suspend current channel only or complete output workflow with a message.
     */
    public function shouldFail(string $failMessage, bool $onlyCurrentChannel = true): void
    {
        $this->failed = true;
        $this->suspended = false;
        $this->currentChannelOnly = $onlyCurrentChannel;
        $this->failMessage = $failMessage;
    }

    /**
     * @internal
     */
    public function isSuspended(): bool
    {
        return $this->suspended === true;
    }

    /**
     * @internal
     */
    public function shouldStopChannel(): bool
    {
        return $this->failed === true && $this->currentChannelOnly === true;
    }

    /**
     * @internal
     */
    public function shouldStopOutputWorkflow(): bool
    {
        return $this->failed === true && $this->currentChannelOnly === false;
    }

    /**
     * @internal
     */
    public function getFailMessage(): ?string
    {
        return $this->failMessage;
    }
}
