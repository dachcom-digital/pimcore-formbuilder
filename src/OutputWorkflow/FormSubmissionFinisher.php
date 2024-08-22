<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\DoubleOptInSubmissionEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Exception\DoubleOptInException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardOutputWorkflowException;
use FormBuilderBundle\Exception\OutputWorkflow\GuardStackedException;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Form\FormErrorsSerializerInterface;
use FormBuilderBundle\Manager\DoubleOptInManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Session\FlashBagManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormSubmissionFinisher implements FormSubmissionFinisherInterface
{
    public function __construct(
        protected FlashBagManagerInterface $flashBagManager,
        protected DoubleOptInManager $doubleOptInManager,
        protected FormErrorsSerializerInterface $formErrorsSerializer,
        protected OutputWorkflowResolverInterface $outputWorkflowResolver,
        protected OutputWorkflowDispatcherInterface $outputWorkflowDispatcher,
        protected SuccessManagementWorkerInterface $successManagementWorker
    ) {
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
            return $this->buildErrorResponse($request, $submissionEvent, 'No valid output workflow found.');
        }

        try {
            $this->outputWorkflowDispatcher->dispatch($outputWorkflow, $submissionEvent);
        } catch (GuardOutputWorkflowException $e) {
            return $this->buildErrorResponse($request, $submissionEvent, $e->getMessage());
        } catch (GuardStackedException $e) {
            return $this->buildErrorResponse($request, $submissionEvent, implode(', ', $e->getGuardExceptionMessages()));
        } catch (\Throwable $e) {

            $errorMessage = sprintf('Error while dispatching workflow "%s". Message was: %s', $outputWorkflow->getName(), $e->getMessage());

            return $this->buildErrorResponse($request, $submissionEvent, $errorMessage);
        }

        if ($outputWorkflow->isFunnelWorkflow()) {
            // A funnel initialization does not provide any success management
            return $this->buildSuccessResponse($request, $submissionEvent);
        }

        if ($outputWorkflow->getSuccessManagement() === null) {
            return $this->buildErrorResponse($request, $submissionEvent, 'No valid success management found.');
        }

        try {
            $this->successManagementWorker->process($submissionEvent, $outputWorkflow->getSuccessManagement());
        } catch (\Throwable $e) {
            $errorMessage = sprintf('Error while processing success management of workflow "%s". Message was: %s', $outputWorkflow->getName(), $e->getMessage());

            return $this->buildErrorResponse($request, $submissionEvent, $errorMessage);
        }

        return $this->buildSuccessResponse($request, $submissionEvent);
    }

    public function finishDoubleOptInWithSuccess(Request $request, DoubleOptInSubmissionEvent $submissionEvent): ?Response
    {
        try {
            $this->doubleOptInManager->processOptInSubmission($submissionEvent);
        } catch (DoubleOptInException $e) {
            return $this->buildErrorResponse($request, $submissionEvent, $e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildErrorResponse($request, $submissionEvent, sprintf('Error while processing double-opt-in: %s', $e->getMessage()));
        }

        return $this->buildSuccessResponse($request, $submissionEvent);
    }

    protected function buildErrorResponse(Request $request, SubmissionEvent|DoubleOptInSubmissionEvent $submissionEvent, ?string $errorMessage): ?Response
    {
        $redirectUri = null;
        $flashBagPrefix = 'formbuilder';

        if ($submissionEvent instanceof SubmissionEvent) {
            /** @var FormDataInterface $data */
            $data = $submissionEvent->getForm()->getData();
            $formDefinition = $data->getFormDefinition();
            $redirectUri = $submissionEvent->hasRedirectUri() ? $submissionEvent->getRedirectUri() : null;

        } else {
            $flashBagPrefix = 'formbuilder_double_opt_in';
            $formDefinition = $submissionEvent->getFormDefinition();
        }

        $arguments = [
            $request,
            $formDefinition,
            $redirectUri,
            $submissionEvent->useFlashBag(),
            $flashBagPrefix,
            $errorMessage
        ];

        return $request->isXmlHttpRequest()
            ? $this->generateAjaxFinisherErrorResponse($errorMessage)
            : $this->generateRedirectFinisherErrorResponse(...$arguments);
    }

    protected function buildSuccessResponse(Request $request, SubmissionEvent|DoubleOptInSubmissionEvent $submissionEvent): ?Response
    {
        $redirectUri = null;
        $flashBagPrefix = 'formbuilder';
        $responseMessages = $submissionEvent->getMessages();

        if ($submissionEvent instanceof SubmissionEvent) {
            /** @var FormDataInterface $data */
            $data = $submissionEvent->getForm()->getData();
            $formDefinition = $data->getFormDefinition();
            $redirectUri = $submissionEvent->hasRedirectUri() ? $submissionEvent->getRedirectUri() : null;
        } else {
            $flashBagPrefix = 'formbuilder_double_opt_in';
            $formDefinition = $submissionEvent->getFormDefinition();
        }

        $arguments = [
            $formDefinition,
            $redirectUri,
            $submissionEvent->useFlashBag(),
            $flashBagPrefix,
            $responseMessages
        ];

        if ($submissionEvent instanceof SubmissionEvent) {
            try {
                $this->doubleOptInManager->redeemDoubleOptInSessionToken($formDefinition, $submissionEvent->getFormRuntimeData());
            } catch(\Throwable $e) {
                return $this->buildErrorResponse($request, $submissionEvent, $e->getMessage());
            }
        }

        return $request->isXmlHttpRequest()
            ? $this->generateAjaxFormSuccessResponse(...$arguments)
            : $this->generateRedirectFormSuccessResponse(...$arguments);
    }

    protected function generateRedirectFormSuccessResponse(
        FormDefinitionInterface $formDefinition,
        ?string $redirectUri,
        bool $useFlashBag,
        string $flashBagPrefix,
        array $responseMessages,
    ): Response {
        $uri = $redirectUri ?? '?send=true';

        if ($useFlashBag === true) {
            foreach ($responseMessages as $type => $eventMessages) {
                foreach ($eventMessages as $message) {

                    $messageKey = $type === 'redirect_message'
                        ? sprintf('%s_redirect_flash_message', $flashBagPrefix)
                        : sprintf('%s_%d_%s', $flashBagPrefix, $formDefinition->getId(), $type);

                    $this->flashBagManager->add($messageKey, $message);
                }
            }
        }

        return new RedirectResponse($uri);
    }

    protected function generateAjaxFormSuccessResponse(
        FormDefinitionInterface $formDefinition,
        ?string $redirectUri,
        bool $useFlashBag,
        string $flashBagPrefix,
        array $responseMessages,
    ): Response {
        $messages = [];
        $error = false;

        foreach ($responseMessages as $type => $eventMessages) {

            if ($type === 'error') {
                $error = true;
            }

            foreach ($eventMessages as $message) {

                if ($type === 'redirect_message' && $useFlashBag === true) {
                    $this->flashBagManager->add(sprintf('%s_redirect_flash_message', $flashBagPrefix), $message);
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
        return new JsonResponse([
            'success'           => false,
            'validation_errors' => $this->formErrorsSerializer->getErrors($form),
        ]);
    }

    protected function generateRedirectFinisherErrorResponse(
        Request $request,
        FormDefinitionInterface $formDefinition,
        ?string $redirectUri,
        bool $useFlashBag,
        string $flashBagPrefix,
        array|string $errors
    ): RedirectResponse {

        $uri = $redirectUri ?? '?send=false';

        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $formDefinitionConfig = $formDefinition->getConfiguration();
        $method = isset($formDefinitionConfig['method']) ? strtoupper($formDefinitionConfig['method']) : 'POST';

        if (in_array($method, ['GET', 'HEAD', 'TRACE'])) {
            $qs = $request->getQueryString();
            if (!empty($qs)) {
                $uri = sprintf('%s%s%s', $uri, !str_contains($uri, '?') ? '?' : '&', $qs);
            }
        }

        if ($useFlashBag === true) {
            $messageKey = sprintf('%s_%s_error', $flashBagPrefix, $formDefinition->getId());
            foreach ($errors as $error) {
                $this->flashBagManager->add($messageKey, $error);
            }
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
            'validation_errors' => [
                'general' => $errors
            ],
        ]);
    }
}
