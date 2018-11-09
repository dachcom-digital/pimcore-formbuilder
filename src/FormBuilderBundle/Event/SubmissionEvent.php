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
    private $formConfiguration;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var null
     */
    private $redirectUri = null;

    /**
     * @param Request       $request
     * @param array         $formConfiguration
     * @param FormInterface $form
     */
    public function __construct(Request $request, $formConfiguration, FormInterface $form)
    {
        $this->request = $request;
        $this->formConfiguration = $formConfiguration;
        $this->form = $form;
    }

    /**
     * @param $uri
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
    public function getFormConfiguration()
    {
        return $this->formConfiguration;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}