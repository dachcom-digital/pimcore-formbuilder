<?php

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Resolver\FormOptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FormAssembleEvent extends Event
{
    protected FormOptionsResolver $formOptionsResolver;
    protected ?FormInterface $form;

    protected array $formData = [];

    public function __construct(FormOptionsResolver $formOptionsResolver, ?FormInterface $form = null)
    {
        $this->formOptionsResolver = $formOptionsResolver;
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

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
    }
}
