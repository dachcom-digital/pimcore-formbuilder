<?php

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Resolver\FormOptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FormAssembleEvent extends Event
{
    protected FormOptionsResolver $formOptionsResolver;
    protected ?FormInterface $form;

    protected array $formData = [];
    protected FormDefinitionInterface $formDefinition;

    public function __construct(FormOptionsResolver $formOptionsResolver, FormDefinitionInterface $formDefinition, ?FormInterface $form = null)
    {
        $this->formOptionsResolver = $formOptionsResolver;
        $this->formDefinition = $formDefinition;
        $this->form = $form;
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
}
