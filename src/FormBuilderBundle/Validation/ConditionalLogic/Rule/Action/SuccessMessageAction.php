<?php

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
