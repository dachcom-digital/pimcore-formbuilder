<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ConstraintsRemoveAction implements ActionInterface
{
    use ActionTrait;

    protected array $fields = [];
    protected array $validation = [];
    protected bool $removeAllValidations = false;

    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        if ($validationState === true) {
            foreach ($this->getFields() as $conditionFieldName) {
                $data[$conditionFieldName] = [];
                if ($this->getRemoveAllValidations() === true) {
                    $data[$conditionFieldName] = 'all';
                } else {
                    foreach ($this->getValidation() as $constraint) {
                        $data[$conditionFieldName][] = $constraint;
                    }
                }
            }
        }

        return new FieldReturnStack('removeConstraints', $data);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getValidation(): array
    {
        return $this->validation;
    }

    public function setValidation(array $validation): void
    {
        $this->validation = $validation;
    }

    public function getRemoveAllValidations(): bool
    {
        return $this->removeAllValidations;
    }

    public function setRemoveAllValidations(bool $removeAllValidations): void
    {
        $this->removeAllValidations = $removeAllValidations;
    }
}
