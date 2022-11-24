<?php

namespace FormBuilderBundle\Model;

interface OutputWorkflowChannelInterface
{
    public function getId(): int;

    public function setType(string $type): void;

    public function getType(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function setConfiguration(array $configuration): void;

    public function getConfiguration(): array;

    public function setFunnelActions(array $funnelActions): void;

    public function getFunnelActions(): array;

    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void;

    public function getOutputWorkflow(): OutputWorkflowInterface;
}
