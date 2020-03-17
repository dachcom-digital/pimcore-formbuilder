<?php

namespace FormBuilderBundle\Model;

class OutputWorkflowChannel implements OutputWorkflowChannelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var OutputWorkflowInterface
     */
    protected $outputWorkflow;

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
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow)
    {
        $this->outputWorkflow = $outputWorkflow;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputWorkflow()
    {
        return $this->outputWorkflow;
    }
}