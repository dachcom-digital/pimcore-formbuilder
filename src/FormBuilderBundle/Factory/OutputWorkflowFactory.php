<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\OutputWorkflow;

class OutputWorkflowFactory implements OutputWorkflowFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createOutputWorkflow()
    {
        return new OutputWorkflow();
    }
}
