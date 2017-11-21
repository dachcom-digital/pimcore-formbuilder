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
    protected $removeAllValidations = FALSE;

    /**
     * @param               $formData
     * @param               $ruleId
     * @return ReturnStackInterface
     */
    public function apply($formData, $ruleId)
    {
        $data = [];
        foreach ($this->getFields() as $conditionFieldName) {
            $data[$conditionFieldName] = [];
            if ($this->getRemoveAllValidations() === TRUE) {
                $data[$conditionFieldName] = 'all';
            } else {
                foreach ($this->getValidation() as $constraint) {
                    $data[$conditionFieldName][] = $constraint;
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
     * @param array
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
     * @param array
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
     * @param bool
     */
    public function setRemoveAllValidations($removeAllValidations)
    {
        $this->removeAllValidations = $removeAllValidations;
    }
}