<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use Symfony\Contracts\EventDispatcher\Event;

class OutputWorkflowSignalEvent extends Event
{
    public const NAME = 'form_builder.output_workflow.signal';

    public function __construct(
        protected string $name,
        protected mixed $data
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
