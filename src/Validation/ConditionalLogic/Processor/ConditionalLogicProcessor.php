<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Validation\ConditionalLogic\Processor;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Registry\ConditionalLogicRegistry;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;

class ConditionalLogicProcessor
{
    public function __construct(protected ConditionalLogicRegistry $conditionalLogicRegistry)
    {
    }

    /**
     * Cycle through each cl block.
     * If $filterField is not null, the action applier requests a FieldReturnStack with valid $fielderField field in return data.
     *
     * @throws \Exception
     */
    public function process(array $options): array
    {
        $formData = $options['formData'] ?? null;
        $conditionalLogic = $options['conditionalLogic'] ?? null;
        $formRuntimeOptions = $options['formRuntimeOptions'] ?? null;
        $field = $options['field'] ?? null;

        if (empty($conditionalLogic)) {
            return [];
        }

        $actionData = [];
        foreach ($conditionalLogic as $ruleId => $ruleData) {
            if (!isset($ruleData['action']) || !isset($ruleData['condition'])) {
                continue;
            }

            $validationState = $this->checkValidity($ruleData['condition'], $formData, $formRuntimeOptions, $ruleId);
            $actionData = array_merge($actionData, $this->applyActions($validationState, $ruleData['action'], $formData, $ruleId, $field));
        }

        return $actionData;
    }

    /**
     * @throws \Exception
     */
    protected function checkValidity(array $conditions, array $formData, array $formRuntimeOptions, int $ruleId): bool
    {
        $valid = true;

        $config = [
            'formRuntimeOptions' => $formRuntimeOptions
        ];

        foreach ($conditions as $condition) {
            //skip condition if there is no php service for it.
            if (!$this->conditionalLogicRegistry->hasCondition($condition['type'])) {
                continue;
            }

            if (!$this->conditionalLogicRegistry->getCondition($condition['type'])->setValues($condition)->isValid($formData, $ruleId, $config)) {
                $valid = false;

                break;
            }
        }

        return $valid;
    }

    /**
     * @throws \Exception
     */
    protected function applyActions(bool $validationState, array $actions, array $formData, int $ruleId, ?FieldDefinitionInterface $field): array
    {
        $returnContainer = [];

        foreach ($actions as $action) {
            //skip action if there is no php service for it.
            if (!$this->conditionalLogicRegistry->hasAction($action['type'])) {
                continue;
            }

            $appliedData = $this->conditionalLogicRegistry->getAction($action['type'])->setValues($action)->apply($validationState, $formData, $ruleId);

            //If field is available: only add affected field data to return container!
            if (!$field instanceof FieldDefinitionInterface) {
                $returnContainer[] = $appliedData;

                continue;
            }

            if (!$appliedData instanceof FieldReturnStack) {
                continue;
            }

            $filterData = [];
            foreach ($appliedData->getData() as $fieldName => $data) {
                if ($fieldName === $field->getName()) {
                    $filterData = $data;
                }
            }

            $appliedData->updateData($filterData);
            $returnContainer[] = $appliedData;
        }

        return $returnContainer;
    }
}
