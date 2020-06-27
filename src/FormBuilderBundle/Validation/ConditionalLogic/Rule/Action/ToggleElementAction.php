<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ToggleElementAction implements ActionInterface
{
    use ActionTrait;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $state = null;

    /**
     * {@inheritdoc}
     */
    public function apply($validationState, $formData, $ruleId)
    {
        $data = [];
        $state = $this->getState();
        $toggleState = $validationState === true ? 'hide' : 'show';

        foreach ($this->getFields() as $conditionFieldName) {
            $data[$conditionFieldName] = $state === $toggleState ? 'fb-cl-hide-element' : '';
        }

        return new FieldReturnStack('toggleElement', $data);
    }

    /**
     * @return array
     *
     * @internal
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     *
     * @internal
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     *
     * @internal
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @internal
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
