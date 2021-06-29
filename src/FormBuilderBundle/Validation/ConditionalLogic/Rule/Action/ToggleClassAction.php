<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ToggleClassAction implements ActionInterface
{
    use ActionTrait;

    protected array $fields = [];
    protected ?string $class = null;

    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        $class = $this->getClass();

        if ($validationState === true) {
            foreach ($this->getFields() as $conditionFieldName) {
                $data[$conditionFieldName] = $class;
            }
        }

        return new FieldReturnStack('toggleClass', $data);
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }
}
