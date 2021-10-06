<?php

namespace FormBuilderBundle\Model;

interface OutputWorkflowChannelInterface
{
    public function getId(): int;

    public function setType(string $type): void;

    public function getType(): string;

    public function setConfiguration(array $configuration): void;

    public function getConfiguration(): array;

    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    public function getOutputWorkflow(): OutputWorkflowInterface;
}
