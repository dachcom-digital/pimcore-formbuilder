<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FormSubmissionFinisherInterface
{
    /**
     * @param Request       $request
     * @param FormInterface $form
     *
     * @return Response|null
     */
    public function finishWithError(Request $request, FormInterface $form);

    /**
     * @param Request         $request
     * @param SubmissionEvent $submissionEvent
     *
     * @return Response|null
     */
    public function finishWithSuccess(Request $request, SubmissionEvent $submissionEvent);
}
