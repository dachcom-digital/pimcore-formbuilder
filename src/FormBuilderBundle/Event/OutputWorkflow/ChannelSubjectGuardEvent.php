<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use FormBuilderBundle\Form\Data\FormDataInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ChannelSubjectGuardEvent extends Event
{
    /**
     * @var FormDataInterface
     */
    protected $formData;

    /**
     * @var mixed
     */
    protected $subject;

    /**
     * @var array
     */
    protected $formRuntimeData;

    /**
     * @var string
     */
    protected $workflowName;

    /**
     * @var string
     */
    protected $channelType;

    /**
     * @var bool
     */
    protected $suspended;

    /**
     * @var bool
     */
    protected $failed;

    /**
     * @var bool
     */
    protected $currentChannelOnly;

    /**
     * @var string
     */
    protected $failMessage;

    /**
     * @param FormDataInterface $formData
     * @param mixed             $subject
     * @param string            $workflowName
     * @param string            $channelType
     * @param array             $formRuntimeData
     */
    public function __construct(FormDataInterface $formData, $subject, string $workflowName, string $channelType, array $formRuntimeData)
    {
        $this->formData = $formData;
        $this->subject = $subject;
        $this->workflowName = $workflowName;
        $this->channelType = $channelType;
        $this->formRuntimeData = $formRuntimeData;

        $this->suspended = false;
        $this->failed = false;
        $this->currentChannelOnly = true;
        $this->failMessage = null;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return FormDataInterface
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @return array
     */
    public function getFormRuntimeData()
    {
        return $this->formRuntimeData;
    }

    /**
     * @return string
     */
    public function getWorkflowName()
    {
        return $this->workflowName;
    }

    /**
     * @return string
     */
    public function getChannelType()
    {
        return $this->channelType;
    }

    /**
     * Silently suspend current process without any notices.
     */
    public function shouldSuspend()
    {
        $this->suspended = true;
        $this->failed = false;
        $this->failMessage = null;
        $this->currentChannelOnly = false;
    }

    /**
     * Suspend current channel only or complete output workflow with a message.
     *
     * @param string $failMessage
     * @param bool   $onlyCurrentChannel
     */
    public function shouldFail(string $failMessage, $onlyCurrentChannel = true)
    {
        $this->failed = true;
        $this->suspended = false;
        $this->currentChannelOnly = $onlyCurrentChannel;
        $this->failMessage = $failMessage;
    }

    /**
     * @return bool
     *
     * @internal
     */
    public function isSuspended()
    {
        return $this->suspended === true;
    }

    /**
     * @return bool
     *
     * @internal
     */
    public function shouldStopChannel()
    {
        return $this->failed === true && $this->currentChannelOnly === true;
    }

    /**
     * @return bool
     *
     * @internal
     */
    public function shouldStopOutputWorkflow()
    {
        return $this->failed === true && $this->currentChannelOnly === false;
    }

    /**
     * @return string|null
     *
     * @internal
     */
    public function getFailMessage()
    {
        return $this->failMessage;
    }
}
