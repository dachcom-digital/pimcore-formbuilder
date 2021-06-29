<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\OutputWorkflow;
use FormBuilderBundle\Model\OutputWorkflowInterface;

class OutputWorkflowFactory implements OutputWorkflowFactoryInterface
{
    public function createOutputWorkflow(): OutputWorkflowInterface
    {
        return new OutputWorkflow();
    }
}
