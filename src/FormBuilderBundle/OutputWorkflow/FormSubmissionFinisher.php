<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardStackedException;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\FormErrorsSerializerInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Session\FlashBagManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class FormSubmissionFinisher implements FormSubmissionFinisherInterface
{
    /**
     * @var FlashBagManagerInterface
     */
    protected $flashBagManager;

    /**
     * @var FormErrorsSerializerInterface
     */
    protected $formErrorsSerializer;

    /**
     * @var OutputWorkflowResolverInterface
     */
    protected $outputWorkflowResolver;

    /**
     * @var OutputWorkflowDispatcherInterface
     */
    protected $outputWorkflowDispatcher;

    /**
     * @var SuccessManagementWorkerInterface
     */
    protected $successManagementWorker;

    /**
     * @param FlashBagManagerInterface          $flashBagManager
     * @param FormErrorsSerializerInterface     $formErrorsSerializer
     * @param OutputWorkflowResolverInterface   $outputWorkflowResolver
     * @param OutputWorkflowDispatcherInterface $outputWorkflowDispatcher
     * @param SuccessManagementWorkerInterface  $successManagementWorker
     */
    public function __construct(
        FlashBagManagerInterface $flashBagManager,
        FormErrorsSerializerInterface $formErrorsSerializer,
        OutputWorkflowResolverInterface $outputWorkflowResolver,
        OutputWorkflowDispatcherInterface $outputWorkflowDispatcher,
        SuccessManagementWorkerInterface $successManagementWorker
    ) {
        $this->flashBagManager = $flashBagManager;
        $this->formErrorsSerializer = $formErrorsSerializer;
        $this->outputWorkflowResolver = $outputWorkflowResolver;
        $this->outputWorkflowDispatcher = $outputWorkflowDispatcher;
        $this->outputWorkflowDispatcher = $outputWorkflowDispatcher;
        $this->successManagementWorker = $successManagementWorker;
    }

    /**
     * {@inheritdoc}
     */
    public function finishWithError(GetResponseEvent $event, FormInterface $form)
    {
        $response = null;
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            $response = $this->generateAjaxFormErrorResponse($form);
        }

        // no need to redirect finished error: we're in a getResponseEvent, let symfony do the rest.
        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishWithSuccess(GetResponseEvent $event, SubmissionEvent $submissionEvent)
    {
        if ($submissionEvent->outputWorkflowFinisherIsDisabled() === true) {
            return;
        }

        $request = $event->getRequest();

        $outputWorkflow = $this->outputWorkflowResolver->resolve($submissionEvent);

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            $errorMessage = 'No valid output workflow found.';
            $event->setResponse($request->isXmlHttpRequest()
                ? $this->generateAjaxFinisherErrorResponse($errorMessage)
                : $this->generateRedirectFinisherErrorResponse($submissionEvent, $errorMessage));

            return;
        }

        try {
            $this->outputWorkflowDispatcher->dispatch($outputWorkflow, $submissionEvent);
        } catch (\Exception $e) {
            if ($e instanceof GuardOutputWorkflowException) {
                $errorMessage = $e->getMessage();
            } elseif ($e instanceof GuardStackedException) {
                $errorMessage = $e->getGuardExceptionMessages();
            } else {
                $errorMessage = sprintf('Error while dispatching workflow "%s". Message was: %s', $outputWorkflow->getName(), $e->getMessage());
            }

            $event->setResponse($request->isXmlHttpRequest()
                ? $this->generateAjaxFinisherErrorResponse($errorMessage)
                : $this->generateRedirectFinisherErrorResponse($submissionEvent, $errorMessage));

            return;
        }

        try {
            $this->successManagementWorker->process($submissionEvent, $outputWorkflow->getSuccessManagement());
        } catch (\Exception $e) {
            $errorMessage = sprintf('Error while processing success management of workflow "%s". Message was: %s', $outputWorkflow->getName(), $e->getMessage());
            $event->setResponse($request->isXmlHttpRequest()
                ? $this->generateAjaxFinisherErrorResponse($errorMessage)
                : $this->generateRedirectFinisherErrorResponse($submissionEvent, $errorMessage));

            return;
        }

        $event->setResponse($request->isXmlHttpRequest()
            ? $this->generateAjaxFormSuccessResponse($submissionEvent)
            : $this->generateRedirectFormSuccessResponse($submissionEvent));
    }

    /**
     * @param SubmissionEvent $submissionEvent
     *
     * @return Response
     */
    protected function generateRedirectFormSuccessResponse(SubmissionEvent $submissionEvent)
    {
        $uri = '?send=true';
        if ($submissionEvent->hasRedirectUri()) {
            $uri = $submissionEvent->getRedirectUri();
        }

        return new RedirectResponse($uri);
    }

    /**
     * @param SubmissionEvent $submissionEvent
     *
     * @return Response
     */
    protected function generateAjaxFormSuccessResponse(SubmissionEvent $submissionEvent)
    {
        $redirectUri = null;
        if ($submissionEvent->hasRedirectUri()) {
            $redirectUri = $submissionEvent->getRedirectUri();
        }

        $messages = [];
        $error = false;

        $form = $submissionEvent->getForm();
        /** @var FormDataInterface $data */
        $data = $form->getData();

        foreach (['success', 'error'] as $type) {
            $messageKey = sprintf('formbuilder_%s_%s', $data->getFormDefinition()->getId(), $type);

            if (!$this->flashBagManager->has($messageKey)) {
                continue;
            }

            foreach ($this->flashBagManager->get($messageKey) as $message) {
                if ($type === 'error') {
                    $error = true;
                }
                $messages[] = ['type' => $type, 'message' => $message];
            }
        }

        return new JsonResponse([
            'success'  => !$error,
            'redirect' => $redirectUri,
            'messages' => $messages
        ]);
    }

    /**
     * @param FormInterface $form
     *
     * @return Response
     */
    protected function generateAjaxFormErrorResponse(FormInterface $form)
    {
        $formattedValidationErrors = $this->formErrorsSerializer->getErrors($form);

        return new JsonResponse([
            'success'           => false,
            'validation_errors' => $formattedValidationErrors,
        ]);
    }

    /**
     * @param SubmissionEvent $submissionEvent
     * @param array|string    $errors
     *
     * @return RedirectResponse
     */
    protected function generateRedirectFinisherErrorResponse(SubmissionEvent $submissionEvent, $errors)
    {
        $uri = '?send=false';
        if ($submissionEvent->hasRedirectUri()) {
            $uri = $submissionEvent->getRedirectUri();
        }

        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $form = $submissionEvent->getForm();
        /** @var FormDataInterface $data */
        $data = $form->getData();

        $messageKey = sprintf('formbuilder_%s_error', $data->getFormDefinition()->getId());

        foreach ($errors as $error) {
            $this->flashBagManager->add($messageKey, $error);
        }

        return new RedirectResponse($uri);
    }

    /**
     * @param array|string $errors
     *
     * @return Response
     */
    protected function generateAjaxFinisherErrorResponse($errors)
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        return new JsonResponse([
            'success'           => false,
            'validation_errors' => ['general' => $errors],
        ]);
    }
}
