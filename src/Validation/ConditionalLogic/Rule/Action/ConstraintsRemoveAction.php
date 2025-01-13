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

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\FieldReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class ConstraintsRemoveAction implements ActionInterface
{
    use ActionTrait;

    protected array $fields = [];
    protected array $validation = [];
    protected bool $removeAllValidations = false;

    /**
     * @throws \Exception
     */
    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
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

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getValidation(): array
    {
        return $this->validation;
    }

    public function setValidation(array $validation): void
    {
        $this->validation = $validation;
    }

    public function getRemoveAllValidations(): bool
    {
        return $this->removeAllValidations;
    }

    public function setRemoveAllValidations(bool $removeAllValidations): void
    {
        $this->removeAllValidations = $removeAllValidations;
    }
}
