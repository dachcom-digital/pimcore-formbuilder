<?php

namespace FormBuilderBundle\Model;

class OutputWorkflowChannel implements OutputWorkflowChannelInterface
{
    protected int $id;
    protected string $type;
    protected array $configuration;
    protected OutputWorkflowInterface $outputWorkflow;

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow): void
    {
        $this->outputWorkflow = $outputWorkflow;
    }

    public function getOutputWorkflow(): OutputWorkflowInterface
    {
        return $this->outputWorkflow;
    }
}
