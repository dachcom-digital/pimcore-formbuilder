<?php

namespace FormBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
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
    private $formRuntimeData;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var null|string
     */
    private $redirectUri = null;

    /**
     * @var bool
     */
    private $outputWorkflowFinisherDisabled = false;

    /**
     * @param Request       $request
     * @param array         $formRuntimeData
     * @param FormInterface $form
     */
    public function __construct(Request $request, $formRuntimeData, FormInterface $form)
    {
        $this->request = $request;
        $this->formRuntimeData = $formRuntimeData;
        $this->form = $form;
    }

    public function disableOutputWorkflowFinisher()
    {
        $this->outputWorkflowFinisherDisabled = true;
    }

    /**
     * @return bool
     */
    public function outputWorkflowFinisherIsDisabled()
    {
        return $this->outputWorkflowFinisherDisabled === true;
    }

    /**
     * @param string $uri
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }

    /**
     * @return mixed
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @return bool
     */
    public function hasRedirectUri()
    {
        return !is_null($this->redirectUri);
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
    public function getFormRuntimeData()
    {
        return $this->formRuntimeData;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
