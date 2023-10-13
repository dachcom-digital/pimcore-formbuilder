<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Condition;

use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Repository\OutputWorkflowRepositoryInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ConditionTrait;

class OutputWorkflowCondition implements ConditionInterface
{
    use ConditionTrait;

    protected array $outputWorkflow = [];

    public function __construct(protected OutputWorkflowRepositoryInterface $outputWorkflowRepository)
    {
    }

    public function isValid(array $formData, int $ruleId, array $configuration = []): bool
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

        $formOutputWorkflowId = $configuration['formRuntimeOptions']['form_output_workflow'];
        $outputWorkflow = $this->outputWorkflowRepository->findById($formOutputWorkflowId);

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            return false;
        }

        return in_array($outputWorkflow->getName(), $this->getOutputWorkflows(), true);
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
