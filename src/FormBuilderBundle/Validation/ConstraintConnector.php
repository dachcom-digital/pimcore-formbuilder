<?php

namespace FormBuilderBundle\Validation;

use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Processor\ConditionalLogicProcessor;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;

class ConstraintConnector
{
    /**
     * @var ConditionalLogicProcessor
     */
    protected $conditionalLogicProcessor;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var array
     */
    protected $formData;

    /**
     * @var FormFieldInterface
     */
    protected $field;

    /**
     * @var array
     */
    protected $availableConstraints;

    /**
     * @var array
     */
    protected $conditionalLogic;

    /**
     * ConstraintConnector constructor.
     *
     * @param ConditionalLogicProcessor $conditionalLogicProcessor
     */
    public function __construct(ConditionalLogicProcessor $conditionalLogicProcessor)
    {
        $this->conditionalLogicProcessor = $conditionalLogicProcessor;
    }

    /**
     * @param                    $formData
     * @param FormFieldInterface $field
     * @param                    $availableConstraints
     * @param                    $conditionalLogic
     * @return array
     */
    public function connect($formData, FormFieldInterface $field, $availableConstraints, $conditionalLogic)
    {
        $this->formData = $formData;
        $this->field = $field;
        $this->availableConstraints = $availableConstraints;
        $this->conditionalLogic = $conditionalLogic;

        //add defaults
        $constraints = [];
        foreach ($this->field->getConstraints() as $constraint) {
            $constraints[] = $constraint['type'];
        }

        $constraints = $this->checkConditionalLogicConstraints($constraints);
        return $this->appendConstraintsData($constraints);
    }

    /**
     * Constraints from current conditional logic
     *
     * @param $defaultFieldConstraints
     * @return array
     */
    public function checkConditionalLogicConstraints($defaultFieldConstraints)
    {
        if (empty($this->conditionalLogic)) {
            return $defaultFieldConstraints;
        }

        $conditionActions = $this->conditionalLogicProcessor->process($this->formData, $this->conditionalLogic, $this->field->getName());

        /** @var ReturnStackInterface $returnStack */
        foreach ($conditionActions as $ruleId => $returnStack) {

            if (!$returnStack instanceof FieldReturnStack || !in_array($returnStack->getActionType(), [
                    'addConstraints',
                    'removeConstraints'
                ])) {
                continue;
            }

            if ($returnStack->getActionType() === 'addConstraints') {
                foreach ($returnStack->getData() as $constraint) {
                    $defaultFieldConstraints[] = $constraint;
                }
            } elseif ($returnStack->getActionType() === 'removeConstraints') {
                if ($returnStack->getData() === 'all') {
                    $defaultFieldConstraints = [];
                } else {
                    foreach ($returnStack->getData() as $constraint) {
                        $defaultFieldConstraints = array_diff($defaultFieldConstraints, [$constraint]);
                    }
                }
            }
        }

        return array_unique($defaultFieldConstraints);
    }

    /**
     * @param $constraints
     * @return array
     */
    public function appendConstraintsData($constraints)
    {
        $constraintData = [];
        foreach ($constraints as $constraint) {

            if (!isset($this->availableConstraints[$constraint])) {
                continue;
            }

            $class = $this->availableConstraints[$constraint]['class'];
            $constraintData[] = new $class();
        }

        return $constraintData;
    }

}