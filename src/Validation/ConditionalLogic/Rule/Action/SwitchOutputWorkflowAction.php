<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class SwitchOutputWorkflowAction implements ActionInterface
{
    use ActionTrait;

    protected ?string $workflowName = null;

    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        if ($validationState === true) {
            $data['workflowName'] = $this->getWorkflowName();
        }

        return new SimpleReturnStack('switchOutputWorkflow', $data);
    }

    public function getWorkflowName(): ?string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): void
    {
        $this->workflowName = $workflowName;
    }
}
