<?php

namespace FormBuilderBundle\Event\OutputWorkflow;

use Symfony\Component\EventDispatcher\Event;

class OutputWorkflowSignalEvent extends Event
{
    public const NAME = 'form_builder.output_workflow.signal';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string $name
     * @param mixed  $data
     */
    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
