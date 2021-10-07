<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class OutputWorkflow implements OutputWorkflowInterface
{
    protected int $id;
    protected string $name;
    protected ?array $successManagement;
    protected FormDefinitionInterface $formDefinition;
    protected Collection $channels;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setSuccessManagement(array $successManagement): void
    {
        $this->successManagement = $successManagement;
    }

    public function getSuccessManagement(): ?array
    {
        return $this->successManagement;
    }

    public function setFormDefinition(FormDefinitionInterface $formDefinition): void
    {
        $this->formDefinition = $formDefinition;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
    }

    public function hasChannels(): bool
    {
        return !$this->channels->isEmpty();
    }

    public function hasChannel(OutputWorkflowChannelInterface $channel): bool
    {
        return $this->channels->contains($channel);
    }

    public function addChannel(OutputWorkflowChannelInterface $channel): void
    {
        if (!$this->hasChannel($channel)) {
            $this->channels->add($channel);
            $channel->setOutputWorkflow($this);
        }
    }

    public function removeChannel(OutputWorkflowChannelInterface $channel): void
    {
        if ($this->hasChannel($channel)) {
            $this->channels->removeElement($channel);
        }
    }

    public function getChannels(): Collection
    {
        return $this->channels;
    }
}
