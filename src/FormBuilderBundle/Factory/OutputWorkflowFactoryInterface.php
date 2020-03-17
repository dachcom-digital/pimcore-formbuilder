<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowFactoryInterface
{
    /**
     * @return OutputWorkflowInterface
     */
    public function createOutputWorkflow();

}
