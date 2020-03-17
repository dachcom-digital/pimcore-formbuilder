<?php

namespace FormBuilderBundle\Model;

interface OutputWorkflowChannelInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $type
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param array $configuration
     *
     * @return mixed
     */
    public function setConfiguration(array $configuration);

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @param OutputWorkflowInterface $outputWorkflow
     */
    public function setOutputWorkflow(OutputWorkflowInterface $outputWorkflow);

    /**
     * @return OutputWorkflowInterface
     */
    public function getOutputWorkflow();
}