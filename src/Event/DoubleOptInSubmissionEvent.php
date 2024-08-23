<?php

namespace FormBuilderBundle\Event;

use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class DoubleOptInSubmissionEvent extends BaseSubmissionEvent
{
    protected FormDefinitionInterface $formDefinition;

    protected ?string $dispatchLocation = null;

    public function __construct(
        Request $request,
        FormDefinitionInterface $formDefinition,
        FormInterface $form,
        bool $useFlashBag = true,
        array $messages = []
    ) {
        parent::__construct(
            $request,
            $form,
            $useFlashBag,
            $messages
        );

        $this->formDefinition = $formDefinition;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
    }

    public function getDispatchLocation(): ?string
    {
        return $this->dispatchLocation;
    }

    public function setDispatchLocation(?string $dispatchLocation): void
    {
        $this->dispatchLocation = $dispatchLocation;
    }
}
