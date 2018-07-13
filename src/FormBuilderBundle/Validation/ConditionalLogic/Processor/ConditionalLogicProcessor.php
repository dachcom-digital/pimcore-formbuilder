<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Processor;

use FormBuilderBundle\Registry\ConditionalLogicRegistry;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;

class ConditionalLogicProcessor
{
    /**
     * @var ConditionalLogicRegistry
     */
    protected $conditionalLogicRegistry;

    /**
     * ConditionalLogicProcessor constructor.
     *
     * @param ConditionalLogicRegistry $conditionalLogicRegistry
     */
    public function __construct(ConditionalLogicRegistry $conditionalLogicRegistry)
    {
        $this->conditionalLogicRegistry = $conditionalLogicRegistry;
    }

    /**
     * Cycle through each cl block.
     * If $filterField is not NULL, the action applier requests a FieldReturnStack with valid $fielderField field in return data.
     *
     * @param      $formData
     * @param      $conditionalLogic
     * @param null $fieldFilter
     * @return array
     * @throws \Exception
     */
    public function process($formData, $conditionalLogic, $fieldFilter = null)
    {
        $actionData = [];
        if (empty($conditionalLogic)) {
            return [];
        }

        foreach ($conditionalLogic as $ruleId => $ruleData) {

            if (!isset($ruleData['action']) || !isset($ruleData['condition'])) {
                continue;
            }

            $validationState = $this->checkValidity($ruleData['condition'], $formData, $ruleId);
            $actionData = array_merge($actionData, $this->applyActions($validationState, $ruleData['action'], $formData, $ruleId, $fieldFilter));
        }

        return $actionData;
    }

    /**
     * @param $conditions
     * @param $formData
     * @param $ruleId
     * @return bool
     * @throws \Exception
     */
    public function checkValidity($conditions, $formData, $ruleId)
    {
        $valid = true;
        foreach ($conditions as $condition) {
            //skip condition if there is no php service for it.
            if (!$this->conditionalLogicRegistry->hasCondition($condition['type'])) {
                continue;
            }

            if (!$this->conditionalLogicRegistry->getCondition($condition['type'])->setValues($condition)->isValid($formData, $ruleId)) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    /**
     * @param $validationState
     * @param $actions
     * @param $formData
     * @param $ruleId
     * @param $fieldFilter
     * @return array
     * @throws \Exception
     */
    public function applyActions($validationState, $actions, $formData, $ruleId, $fieldFilter)
    {
        $returnContainer = [];
        foreach ($actions as $action) {
            //skip action if there is no php service for it.
            if (!$this->conditionalLogicRegistry->hasAction($action['type'])) {
                continue;
            }

            $appliedData = $this->conditionalLogicRegistry->getAction($action['type'])->setValues($action)->apply($validationState, $formData, $ruleId);

            //Field Filter is active: only add affected field data to return container!
            if ($fieldFilter instanceof FormFieldInterface) {

                if (!$appliedData instanceof FieldReturnStack) {
                    continue;
                }

                $filterData = [];
                foreach ($appliedData->getData() as $fieldName => $data) {
                    if ($fieldName === $fieldFilter->getName()) {
                        $filterData = $data;
                    }
                }

                $appliedData->updateData($filterData);
                $returnContainer[] = $appliedData;
            } else {
                if ($appliedData instanceof ReturnStackInterface) {
                    $returnContainer[] = $appliedData;
                }
            }
        }
        return $returnContainer;
    }
}
