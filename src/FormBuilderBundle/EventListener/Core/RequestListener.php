<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Builder;
use FormBuilderBundle\FormBuilderEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var Builder
     */
    protected $formBuilder;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * RequestListener constructor.
     *
     * @param Builder                  $formBuilder
     * @param EventDispatcherInterface $eventDispatcher
     * @param SessionInterface         $session
     */
    public function __construct(
        Builder $formBuilder,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session
    ) {
        $this->formBuilder = $formBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
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
        if (!$request->isMethod('POST')) {
            return;
        }

        try {
            /** @var FormInterface $form */
            list($formId, $form) = $this->formBuilder->buildByRequest($request);
            if (!$form instanceof FormInterface) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        if ($form->isSubmitted()) {

            /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
            $sessionBag = $this->session->getBag('form_builder_session');

            if ($form->isValid()) {

                $formConfiguration = [];
                if ($sessionBag->has('form_configuration_' . $formId)) {
                    $formConfiguration = $sessionBag->get('form_configuration_' . $formId);
                    $sessionBag->remove('form_configuration_' . $formId);
                }

                $submissionEvent = new SubmissionEvent($request, $formConfiguration, $form);
                $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_SUBMIT_SUCCESS, $submissionEvent);

                if ($request->isXmlHttpRequest()) {
                    $this->handleAjaxSuccessResponse($event, $submissionEvent, $form);
                } else {
                    $this->handleDefaultSuccessResponse($event, $submissionEvent, $form);
                }
            } else {

                //only ajax forms want some feedback.
                if ($request->isXmlHttpRequest()) {
                    $this->handleAjaxErrorResponse($event, $form);
                }
            }
        }
    }

    /**
     * @param                      $event
     * @param SubmissionEvent|null $submissionEvent
     * @param                      $form
     */
    protected function handleDefaultSuccessResponse(GetResponseEvent $event, SubmissionEvent $submissionEvent, FormInterface $form)
    {
        $uri = '?send=true';
        if ($submissionEvent->hasRedirectUri()) {
            $uri = $submissionEvent->getRedirectUri();
        }

        $response = new RedirectResponse($uri);
        $event->setResponse($response);
    }

    /**
     * @param GetResponseEvent $event
     * @param SubmissionEvent  $submissionEvent
     * @param FormInterface    $form
     */
    protected function handleAjaxSuccessResponse(GetResponseEvent $event, SubmissionEvent $submissionEvent, FormInterface $form)
    {
        $redirectUri = NULL;
        if ($submissionEvent->hasRedirectUri()) {
            $redirectUri = $submissionEvent->getRedirectUri();
        }

        $messages = [];
        $error = FALSE;
        $flashBag = $this->session->getFlashBag();

        foreach ($flashBag->all() as $type => $message) {
            if ($type === 'error') {
                $error = TRUE;
            }
            $messages[] = ['type' => $type, 'message' => $message];
        }

        $response = new JsonResponse([
            'success'  => !$error,
            'redirect' => $redirectUri,
            'messages' => $messages
        ]);

        $event->setResponse($response);
    }

    /**
     * @param GetResponseEvent $event
     * @param FormInterface    $form
     */
    protected function handleAjaxErrorResponse(GetResponseEvent $event, FormInterface $form)
    {
        $response = new JsonResponse([
            'success'           => FALSE,
            'validation_errors' => $this->getErrors($form),
        ]);

        $event->setResponse($response);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getErrors(FormInterface $form)
    {
        $errors = [];

        $generalErrors = [];
        foreach ($form->getErrors() as $error) {
            $generalErrors[] = $error->getMessage();
        }

        if (!empty($generalErrors)) {
            $errors['general'] = $generalErrors;
        }

        foreach ($form->all() as $field) {
            $fieldErrors = [];

            foreach ($field->getErrors() as $error) {
                $fieldErrors[] = $error->getMessage();
            }

            if (!empty($fieldErrors)) {
                $errors[$field->getName()] = $fieldErrors;
            }
        }

        return $errors;
    }
}
