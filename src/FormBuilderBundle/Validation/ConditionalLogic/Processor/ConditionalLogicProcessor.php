<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Processor;

use FormBuilderBundle\Registry\ConditionalLogicRegistry;
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
     * If $filterField is not NULL, the action applier requets a FieldReturnStack with valid $fielderField field in return data.
     *
     * @param $formData
     * @param $conditionalLogic
     * @param $fieldFilter
     *
     * @return ReturnStackInterface[] array
     */
    public function process($formData, $conditionalLogic, $fieldFilter = NULL)
    {
        $actionData = [];
        if (empty($conditionalLogic)) {
            return [];
        }

        foreach ($conditionalLogic as $ruleId => $ruleData) {

            if (!isset($ruleData['action']) || !isset($ruleData['condition'])) {
                continue;
            }

            if ($this->checkValidity($ruleData['condition'], $formData, $ruleId)) {
                $actionData = array_merge($actionData, $this->applyActions($ruleData['action'], $formData, $ruleId, $fieldFilter));
            }
        }

        return $actionData;
    }

    /**
     * @param $conditions
     * @param $formData
     * @param $ruleId
     *
     * @return bool
     */
    public function checkValidity($conditions, $formData, $ruleId)
    {
        $valid = TRUE;
        foreach ($conditions as $condition) {
            //skip condition if there is no php service for it.
            if (!$this->conditionalLogicRegistry->hasCondition($condition['type'])) {
                continue;
            }

            if (!$this->conditionalLogicRegistry->getCondition($condition['type'])->setValues($condition)->isValid($formData, $ruleId)) {
                $valid = FALSE;
                break;
            }
        }

        return $valid;
    }

    /**
     * @param $actions
     * @param $formData
     * @param $ruleId
     * @param $fieldFilter
     *
     * @return array
     */
    public function applyActions($actions, $formData, $ruleId, $fieldFilter)
    {
        $returnContainer = [];
        foreach ($actions as $action) {
            //skip action if there is no php service for it.
            if (!$this->conditionalLogicRegistry->hasAction($action['type'])) {
                continue;
            }

            $appliedData = $this->conditionalLogicRegistry->getAction($action['type'])->setValues($action)->apply($formData, $ruleId);

            //Field Filter is active: only add affected field data to return container!
            if ($fieldFilter !== NULL) {

                if (!$appliedData instanceof FieldReturnStack) {
                    continue;
                }

                $filterData = [];
                foreach ($appliedData->getData() as $fieldName => $data) {
                    if ($fieldName === $fieldFilter) {
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
