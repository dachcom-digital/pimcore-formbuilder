<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\OutputWorkflow\FormSubmissionFinisherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FormSubmissionFinisherInterface
     */
    protected $formSubmissionFinisher;

    /**
     * @param FrontendFormBuilder             $frontendFormBuilder
     * @param EventDispatcherInterface        $eventDispatcher
     * @param SessionInterface                $session
     * @param FormSubmissionFinisherInterface $formSubmissionFinisher
     */
    public function __construct(
        FrontendFormBuilder $frontendFormBuilder,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        FormSubmissionFinisherInterface $formSubmissionFinisher
    ) {
        $this->frontendFormBuilder = $frontendFormBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->formSubmissionFinisher = $formSubmissionFinisher;
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
        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');
        $formConfiguration = [];

        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod('POST')) {
            return;
        }

        $formId = $this->frontendFormBuilder->findFormIdByRequest($request);
        if (is_null($formId)) {
            return;
        }

        if ($sessionBag->has('form_configuration_' . $formId)) {
            $formConfiguration = $sessionBag->get('form_configuration_' . $formId);
        }

        try {
            $userOptions = isset($formConfiguration['form_runtime_options']) ? $formConfiguration['form_runtime_options'] : [];
            $form = $this->frontendFormBuilder->buildForm($formId, $userOptions);
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                $response = new JsonResponse([
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTrace()
                ]);
                $event->setResponse($response);
            }

            return;
        }

        if (!$form->isSubmitted()) {
            return;
        }

        if (!$form->isValid()) {
            $this->formSubmissionFinisher->finishWithError($event, $form);
            return;
        }

        if ($sessionBag->has('form_configuration_' . $formId)) {
            $sessionBag->remove('form_configuration_' . $formId);
        }

        $submissionEvent = new SubmissionEvent($request, $formConfiguration, $form);
        $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_SUBMIT_SUCCESS, $submissionEvent);

        $this->formSubmissionFinisher->finishWithSuccess($event, $submissionEvent);
    }
}
