<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class SwitchOutputWorkflowAction implements ActionInterface
{
    use ActionTrait;

    protected ?string $workflowId = null;

    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        if ($validationState === true) {
            $data['workflowId'] = $this->getWorkflowId();
        }

        return new SimpleReturnStack('switchOutputWorkflow', $data);
    }

    public function getWorkflowId(): ?string
    {
        return $this->workflowId;
    }

    public function setWorkflowId(string $workflowId): void
    {
        $this->workflowId = $workflowId;
    }
}
