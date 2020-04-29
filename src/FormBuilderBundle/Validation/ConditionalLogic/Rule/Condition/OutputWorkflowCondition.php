<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ConditionTrait;

class OutputWorkflowCondition implements ConditionInterface
{
    use ConditionTrait;

    /**
     * @var array
     */
    protected $outputWorkflow = [];

    /**
     * {@inheritDoc}
     */
    public function isValid($formData, $ruleId, $configuration = [])
    {
        // ignore
        if (!isset($configuration['formRuntimeOptions'])) {
            return true;
        }

        // ignore
        if (!isset($configuration['formRuntimeOptions']['form_output_workflow'])) {
            return true;
        }

        // ignore
        if (!is_numeric($configuration['formRuntimeOptions']['form_output_workflow'])) {
            return true;
        }

        return in_array($configuration['formRuntimeOptions']['form_output_workflow'], $this->getOutputWorkflows());
    }

    /**
     * @return array
     * @internal
     */
    public function getOutputWorkflows()
    {
        return $this->outputWorkflow;
    }

    /**
     * @param array $outputWorkflow
     *
     * @internal
     */
    public function setOutputWorkflows($outputWorkflow)
    {
        $this->outputWorkflow = $outputWorkflow;
    }
}
