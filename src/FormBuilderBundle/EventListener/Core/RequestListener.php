<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\OutputWorkflow\FormSubmissionFinisherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var FrontendFormBuilder
     */
    protected $frontendFormBuilder;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var FormSubmissionFinisherInterface
     */
    protected $formSubmissionFinisher;

    /**
     * @var FormDefinitionManager
     */
    protected $formDefinitionManager;

    /**
     * @param FrontendFormBuilder             $frontendFormBuilder
     * @param EventDispatcherInterface        $eventDispatcher
     * @param FormSubmissionFinisherInterface $formSubmissionFinisher
     * @param FormDefinitionManager           $formDefinitionManager
     */
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
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

    /**
     * @param GetResponseEvent $event
     * @param FormInterface    $form
     */
    protected function doneWithError(GetResponseEvent $event, FormInterface $form)
    {
        $request = $event->getRequest();
        $finishResponse = $this->formSubmissionFinisher->finishWithError($request, $form);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    /**
     * @param GetResponseEvent $event
     * @param FormInterface    $form
     * @param array|null       $formRuntimeData
     */
    protected function doneWithSuccess(GetResponseEvent $event, FormInterface $form, $formRuntimeData)
    {
        $request = $event->getRequest();
        $submissionEvent = new SubmissionEvent($request, $formRuntimeData, $form);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_SUBMIT_SUCCESS, $submissionEvent);

        $finishResponse = $this->formSubmissionFinisher->finishWithSuccess($request, $submissionEvent);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    /**
     * @param GetResponseEvent $event
     * @param \Exception|null  $e
     * @param string|null      $message
     */
    protected function generateErroredJsonReturn(GetResponseEvent $event, ?\Exception $e, string $message = null)
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

    /**
     * @param Request $request
     *
     * @return null|int
     */
    public function findFormIdByRequest(Request $request)
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

    /**
     * @param Request                 $request
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array|null
     */
    protected function detectFormRuntimeDataInRequest(Request $request, FormDefinitionInterface $formDefinition)
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
