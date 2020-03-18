<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

interface FormSubmissionFinisherInterface
{
    /**
     * @param GetResponseEvent $event
     * @param FormInterface    $form
     */
    public function finishWithError(GetResponseEvent $event, FormInterface $form);

    /**
     * @param GetResponseEvent $event
     * @param SubmissionEvent  $submissionEvent
     */
    public function finishWithSuccess(GetResponseEvent $event, SubmissionEvent $submissionEvent);
}