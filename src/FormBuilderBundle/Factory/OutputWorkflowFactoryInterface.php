<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowFactoryInterface
{
    public function createOutputWorkflow(): OutputWorkflowInterface;
}
