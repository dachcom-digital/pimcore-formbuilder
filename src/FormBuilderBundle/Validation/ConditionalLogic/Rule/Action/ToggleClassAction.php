<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ToggleClassAction implements ActionInterface
{
    use ActionTrait;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $class = NULL;

    /**
     * @param               $validationState
     * @param               $formData
     * @param               $ruleId
     * @return ReturnStackInterface
     */
    public function apply($validationState, $formData, $ruleId)
    {
        $data = [];
        $class = $this->getClass();

        if ($validationState === TRUE) {
            foreach ($this->getFields() as $conditionFieldName) {
                $data[$conditionFieldName] = $class;
            }
        }

        return new FieldReturnStack('toggleClass', $data);

    }

    /**
     * @param array
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}