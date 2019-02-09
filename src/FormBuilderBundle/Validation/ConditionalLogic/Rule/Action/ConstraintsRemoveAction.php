<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ConstraintsRemoveAction implements ActionInterface
{
    use ActionTrait;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $validation = [];

    /**
     * @var bool
     */
    protected $removeAllValidations = false;

    /**
     * @param bool  $validationState
     * @param array $formData
     * @param int   $ruleId
     *
     * @return FieldReturnStack|ReturnStackInterface
     * @throws \Exception
     */
    public function apply($validationState, $formData, $ruleId)
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

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param array $validation
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    /**
     * @return bool
     */
    public function getRemoveAllValidations()
    {
        return $this->removeAllValidations;
    }

    /**
     * @param bool $removeAllValidations
     */
    public function setRemoveAllValidations($removeAllValidations)
    {
        $this->removeAllValidations = $removeAllValidations;
    }
}