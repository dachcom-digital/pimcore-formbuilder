<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\DoubleOptInSubmissionEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FormSubmissionFinisherInterface
{
    public function finishWithError(Request $request, FormInterface $form): ?Response;

    public function finishWithSuccess(Request $request, SubmissionEvent $submissionEvent): ?Response;

    public function finishDoubleOptInWithSuccess(Request $request, DoubleOptInSubmissionEvent $submissionEvent): ?Response;
}
