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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormSubmissionFinisher implements FormSubmissionFinisherInterface
{
    protected FlashBagManagerInterface $flashBagManager;
    protected FormErrorsSerializerInterface $formErrorsSerializer;
    protected OutputWorkflowResolverInterface $outputWorkflowResolver;
    protected OutputWorkflowDispatcherInterface $outputWorkflowDispatcher;
    protected SuccessManagementWorkerInterface $successManagementWorker;

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
        $this->successManagementWorker = $successManagementWorker;
    }

    public function finishWithError(Request $request, FormInterface $form): ?Response
    {
        $response = null;

        if ($request->isXmlHttpRequest()) {
            $response = $this->generateAjaxFormErrorResponse($form);
        }

        return $response;
    }

    public function finishWithSuccess(Request $request, SubmissionEvent $submissionEvent): ?Response
    {
        if ($submissionEvent->outputWorkflowFinisherIsDisabled() === true) {
            return null;
        }

        $outputWorkflow = $this->outputWorkflowResolver->resolve($submissionEvent);

        if (!$outputWorkflow instanceof OutputWorkflowInterface) {
            $errorMessage = 'No valid output workflow found.';

            return $request->isXmlHttpRequest()
                ? $this->generateAjaxFinisherErrorResponse($errorMessage)
                : $this->generateRedirectFinisherErrorResponse($submissionEvent, $errorMessage);
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

            return $request->isXmlHttpRequest()
                ? $this->generateAjaxFinisherErrorResponse($errorMessage)
                : $this->generateRedirectFinisherErrorResponse($submissionEvent, $errorMessage);
        }

        try {
            $this->successManagementWorker->process($submissionEvent, $outputWorkflow->getSuccessManagement());
        } catch (\Exception $e) {
            $errorMessage = sprintf('Error while processing success management of workflow "%s". Message was: %s', $outputWorkflow->getName(), $e->getMessage());

            return $request->isXmlHttpRequest()
                ? $this->generateAjaxFinisherErrorResponse($errorMessage)
                : $this->generateRedirectFinisherErrorResponse($submissionEvent, $errorMessage);
        }

        return $request->isXmlHttpRequest()
            ? $this->generateAjaxFormSuccessResponse($submissionEvent)
            : $this->generateRedirectFormSuccessResponse($submissionEvent);
    }

    protected function generateRedirectFormSuccessResponse(SubmissionEvent $submissionEvent): Response
    {
        $uri = '?send=true';
        if ($submissionEvent->hasRedirectUri()) {
            $uri = $submissionEvent->getRedirectUri();
        }

        return new RedirectResponse($uri);
    }

    protected function generateAjaxFormSuccessResponse(SubmissionEvent $submissionEvent): Response
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

    protected function generateAjaxFormErrorResponse(FormInterface $form): Response
    {
        $formattedValidationErrors = $this->formErrorsSerializer->getErrors($form);

        return new JsonResponse([
            'success'           => false,
            'validation_errors' => $formattedValidationErrors,
        ]);
    }

    protected function generateRedirectFinisherErrorResponse(SubmissionEvent $submissionEvent, array|string $errors): RedirectResponse
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

        $formDefinition = $data->getFormDefinition();
        $formDefinitionConfig = $formDefinition->getConfiguration();
        $method = isset($formDefinitionConfig['method']) ? strtoupper($formDefinitionConfig['method']) : 'POST';

        if (in_array($method, ['GET', 'HEAD', 'TRACE'])) {
            $qs = $submissionEvent->getRequest()->getQueryString();
            if (!empty($qs)) {
                $uri = !str_contains($uri, '?') ? ($uri . '?' . $qs) : ($uri . '&' . $qs);
            }
        }

        $messageKey = sprintf('formbuilder_%s_error', $data->getFormDefinition()->getId());

        foreach ($errors as $error) {
            $this->flashBagManager->add($messageKey, $error);
        }

        return new RedirectResponse($uri);
    }

    protected function generateAjaxFinisherErrorResponse(array|string $errors): Response
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
