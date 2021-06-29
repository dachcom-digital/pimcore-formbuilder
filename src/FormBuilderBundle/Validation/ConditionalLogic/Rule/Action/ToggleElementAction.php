<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ToggleElementAction implements ActionInterface
{
    use ActionTrait;

    protected array $fields = [];
    protected ?string $state = null;

    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        $state = $this->getState();
        $toggleState = $validationState === true ? 'hide' : 'show';

        foreach ($this->getFields() as $conditionFieldName) {
            $data[$conditionFieldName] = $state === $toggleState ? 'fb-cl-hide-element' : '';
        }

        return new FieldReturnStack('toggleElement', $data);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }
}
