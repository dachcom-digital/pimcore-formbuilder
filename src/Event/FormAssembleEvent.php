<?php

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FormAssembleEvent extends Event
{
    protected array $formData = [];

    public function __construct(
        protected FormOptionsResolver $formOptionsResolver,
        protected FormDefinitionInterface $formDefinition,
        protected ?FormInterface $form = null,
        protected bool $headless = false
    ) {
    }

    public function getFormOptionsResolver(): FormOptionsResolver
    {
        return $this->formOptionsResolver;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
    }

    public function isHeadless(): bool
    {
        return $this->headless;
    }
}
