<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Event\OutputWorkflow;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\OutputWorkflow\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class ChannelSubjectGuardEvent extends Event
{
    protected bool $suspended = false;
    protected bool $failed = false;
    protected bool $currentChannelOnly = true;
    protected ?string $failMessage = null;

    public function __construct(
        protected FormDataInterface $formData,
        protected mixed $subject,
        protected string $workflowName,
        protected string $channelType,
        protected array $formRuntimeData,
        protected ?ChannelContext $channelContext = null
    ) {
    }

    public function setSubject(mixed $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): mixed
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

    public function hasChannelContext(): bool
    {
        return $this->channelContext instanceof ChannelContext;
    }

    public function getChannelContext(): ChannelContext
    {
        if (!$this->hasChannelContext()) {
            throw new \RuntimeException('ChannelContext not available');
        }

        return $this->channelContext;
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
