<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\Collection;

interface OutputWorkflowInterface
{
    public function getId(): int;

    public function setName(string $name): void;

    public function getName(): string;

    public function setSuccessManagement(array $successManagement): void;

    public function getSuccessManagement(): ?array;

    public function setFormDefinition(FormDefinitionInterface $formDefinition): void;

    public function getFormDefinition(): FormDefinitionInterface;

    public function hasChannels(): bool;

    public function hasChannel(OutputWorkflowChannelInterface $channel): bool;

    public function addChannel(OutputWorkflowChannelInterface $channel): void;

    public function removeChannel(OutputWorkflowChannelInterface $channel): void;

    /**
     * @return Collection<int, OutputWorkflowChannelInterface>
     */
    public function getChannels(): Collection;
}
