<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Rule\Action;

use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\ReturnStackInterface;
use FormBuilderBundle\Validation\ConditionalLogic\ReturnStack\SimpleReturnStack;
use FormBuilderBundle\Validation\ConditionalLogic\Rule\Traits\ActionTrait;

class MailBehaviourAction implements ActionInterface
{
    use ActionTrait;

    protected ?string $identifier = null;
    protected ?string $value = null;
    protected ?string $mailType = null;

    /**
     * @param bool  $validationState
     * @param array $formData
     * @param int   $ruleId
     *
     * @return ReturnStackInterface
     */
    public function apply(bool $validationState, array $formData, int $ruleId): ReturnStackInterface
    {
        $data = [];
        if ($validationState === true) {
            $data['identifier'] = $this->getIdentifier();
            $data['value'] = $this->getValue();
            $data['mailType'] = $this->getMailType();
        }

        return new SimpleReturnStack('mailBehaviour', $data);
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getMailType(): ?string
    {
        return $this->mailType;
    }

    public function setMailType(string $mailType): void
    {
        $this->mailType = $mailType;
    }
}
