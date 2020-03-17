<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OutputWorkflow implements OutputWorkflowInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $successManagement;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Collection|OutputWorkflowChannelInterface[]
     */
    protected $channels;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuccessManagement(array $successManagement)
    {
        $this->successManagement = $successManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessManagement()
    {
        return $this->successManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannels()
    {
        return !$this->channels->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(OutputWorkflowChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(OutputWorkflowChannelInterface $channel)
    {
        if (!$this->hasChannel($channel)) {
            $this->channels->add($channel);
            $channel->setOutputWorkflow($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(OutputWorkflowChannelInterface $channel)
    {
        if ($this->hasChannel($channel)) {
            $this->channels->removeElement($channel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return $this->channels;
    }
}