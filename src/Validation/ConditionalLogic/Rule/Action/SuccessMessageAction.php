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

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class SuccessMessageAction implements ActionInterface
{
    use ActionTrait;

    protected ?string $identifier = null;
    protected array|string|null $value = null;
    protected ?string $flashMessage = null;

    /**
     * {@inheritDoc}
     */
    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        if ($validationState === true) {
            $data['identifier'] = $this->getIdentifier();
            $data['value'] = $this->getValue();
            $data['flashMessage'] = $this->getFlashMessage();
        }

        return new SimpleReturnStack('successMessage', $data);
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getValue(): array|string|null
    {
        return $this->value;
    }

    public function setValue(array|string $value): void
    {
        $this->value = $value;
    }

    public function getFlashMessage(): ?string
    {
        return $this->flashMessage;
    }

    public function setFlashMessage(string $flashMessage): void
    {
        $this->flashMessage = $flashMessage;
    }
}
