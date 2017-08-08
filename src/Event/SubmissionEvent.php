<?php

namespace FormBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class SubmissionEvent extends Event
{
    /**
     * @var array
     */
    private $formConfiguration;

    /**
     * @var array
     */
    private $formData;

    /**
     * @param array $formConfiguration
     * @param array  $formData
     */
    public function __construct($formConfiguration = [], $formData = [])
    {
        $this->formConfiguration = $formConfiguration;
        $this->formData = $formData;
    }

    /**
     * @return array
     */
    public function getFormConfiguration()
    {
        return $this->formConfiguration;
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        return $this->formData;
    }

}