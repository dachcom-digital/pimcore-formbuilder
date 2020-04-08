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
    public function finishWithError(Request $request, FormInterface $form)
    {
        $response = null;

        if ($request->isXmlHttpRequest()) {
            $response = $this->generateAjaxFormErrorResponse($form);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function finishWithSuccess(Request $request, SubmissionEvent $submissionEvent)
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

        $formDefinition = $data->getFormDefinition();
        $formDefinitionConfig = $formDefinition->getConfig();
        $method = isset($formDefinitionConfig['method']) ? strtoupper($formDefinitionConfig['method']) : 'POST';

        if (in_array($method, ['GET', 'HEAD', 'TRACE'])) {
            $qs = $submissionEvent->getRequest()->getQueryString();
            if (!empty($qs)) {
                $uri = strpos($uri, '?' === false) ? ($uri . '?' . $qs) : ($uri . '&' . $qs);
            }
        }

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
