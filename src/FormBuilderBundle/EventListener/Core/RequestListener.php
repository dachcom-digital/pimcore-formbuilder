<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\OutputWorkflow\FormSubmissionFinisherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    protected FrontendFormBuilder $frontendFormBuilder;
    protected EventDispatcherInterface $eventDispatcher;
    protected FormSubmissionFinisherInterface $formSubmissionFinisher;
    protected FormDefinitionManager $formDefinitionManager;

    public function __construct(
        FrontendFormBuilder $frontendFormBuilder,
        EventDispatcherInterface $eventDispatcher,
        FormSubmissionFinisherInterface $formSubmissionFinisher,
        FormDefinitionManager $formDefinitionManager
    ) {
        $this->frontendFormBuilder = $frontendFormBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->formSubmissionFinisher = $formSubmissionFinisher;
        $this->formDefinitionManager = $formDefinitionManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $formId = $this->findFormIdByRequest($request);

        if ($formId === null) {
            return;
        }

        $formDefinition = $this->formDefinitionManager->getById($formId);
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return;
        }

        try {
            $formRuntimeData = $this->detectFormRuntimeDataInRequest($event->getRequest(), $formDefinition);
            $form = $this->frontendFormBuilder->buildForm($formDefinition, $formRuntimeData);
        } catch (\Exception $e) {
            $this->generateErroredJsonReturn($event, $e);

            return;
        }

        if (!$form->isSubmitted()) {
            return;
        }

        if ($form->isValid() === false) {
            $this->doneWithError($event, $form);
        } else {
            $this->doneWithSuccess($event, $form, $formRuntimeData);
        }
    }

    protected function doneWithError(GetResponseEvent $event, FormInterface $form): void
    {
        $request = $event->getRequest();
        $finishResponse = $this->formSubmissionFinisher->finishWithError($request, $form);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    protected function doneWithSuccess(RequestEvent $event, FormInterface $form, ?array $formRuntimeData = null)
    {
        $request = $event->getRequest();
        $submissionEvent = new SubmissionEvent($request, $formRuntimeData, $form);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_SUBMIT_SUCCESS, $submissionEvent);

        $finishResponse = $this->formSubmissionFinisher->finishWithSuccess($request, $submissionEvent);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    protected function generateErroredJsonReturn(RequestEvent $event, ?\Exception $e, string $message = null): void
    {
        $request = $event->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return;
        }

        $response = new JsonResponse([
            'success' => false,
            'error'   => $e instanceof \Exception ? $e->getMessage() : $message,
            'trace'   => $e instanceof \Exception ? $e->getTrace() : [],
        ]);

        $event->setResponse($response);
    }

    public function findFormIdByRequest(Request $request): ?int
    {
        $isProcessed = false;
        $data = null;

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
        } elseif (in_array($request->getMethod(), ['GET', 'HEAD', 'TRACE'])) {
            $isProcessed = $request->query->has('send');
            $data = $request->query->all();
        }

        if ($isProcessed === true) {
            return null;
        }

        if (empty($data)) {
            return null;
        }

        foreach ($data as $key => $parameters) {
            if (strpos($key, 'formbuilder_') === false) {
                continue;
            }

            if (isset($parameters['formId'])) {
                return $parameters['formId'];
            }
        }

        return null;
    }

    protected function detectFormRuntimeDataInRequest(Request $request, FormDefinitionInterface $formDefinition): ?array
    {
        $formDefinitionConfig = $formDefinition->getConfig();

        $data = null;
        $name = sprintf('formbuilder_%s', $formDefinition->getId());
        $method = isset($formDefinitionConfig['method']) ? strtoupper($formDefinitionConfig['method']) : 'POST';

        if ($request->getMethod() !== $method) {
            return [];
        }

        if (in_array($method, ['GET', 'HEAD', 'TRACE']) && $request->query->has($name)) {
            $data = $request->query->get($name);
        } elseif ($request->request->has($name)) {
            $data = $request->request->get($name, null);
        }

        if (!is_array($data)) {
            return null;
        }

        if (isset($data['formRuntimeData']) && is_string($data['formRuntimeData'])) {
            return json_decode($data['formRuntimeData'], true);
        }

        return null;
    }
}
