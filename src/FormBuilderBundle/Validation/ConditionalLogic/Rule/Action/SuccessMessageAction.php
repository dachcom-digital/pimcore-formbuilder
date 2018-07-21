<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class SuccessMessageAction implements ActionInterface
{
    use ActionTrait;

    /**
     * @var string
     */
    protected $identifier = null;

    /**
     * @var string
     */
    protected $value = null;

    /**
     * @param               $validationState
     * @param               $formData
     * @param               $ruleId
     * @return ReturnStackInterface
     */
    public function apply($validationState, $formData, $ruleId)
    {
        $data = [];
        if ($validationState === true) {
            $data['identifier'] = $this->getIdentifier();
            $data['value'] = $this->getValue();
        }

        return new SimpleReturnStack('successMessage', $data);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}