<?php

namespace FormBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $formConfiguration;

    /**
     * @var array
     */
    private $formData;

    /**
     * @param Request $request
     * @param array $formConfiguration
     * @param array  $formData
     */
    public function __construct(Request $request, $formConfiguration = [], $formData = [])
    {
        $this->request = $request;
        $this->formConfiguration = $formConfiguration;
        $this->formData = $formData;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
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