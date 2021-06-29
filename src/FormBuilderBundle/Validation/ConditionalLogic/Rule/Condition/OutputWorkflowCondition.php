<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ConditionTrait;

class OutputWorkflowCondition implements ConditionInterface
{
    use ConditionTrait;

    protected array $outputWorkflow = [];

    public function isValid(array $formData, bool $ruleId, array $configuration = []): bool
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

    public function getOutputWorkflows(): array
    {
        return $this->outputWorkflow;
    }

    public function setOutputWorkflows(array $outputWorkflow): void
    {
        $this->outputWorkflow = $outputWorkflow;
    }
}
