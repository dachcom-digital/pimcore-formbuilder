<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Builder;
use FormBuilderBundle\FormBuilderEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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
        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');
        $formConfiguration = [];

        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod('POST')) {
            return;
        }

        $formId = $this->formBuilder->detectedFormIdByRequest($request);
        if (is_null($formId)) {
            return;
        }

        if ($sessionBag->has('form_configuration_' . $formId)) {
            $formConfiguration = $sessionBag->get('form_configuration_' . $formId);
        }

        try {
            $userOptions = isset($formConfiguration['user_options']) ? $formConfiguration['user_options'] : [];
            $form = $this->formBuilder->buildForm($formId, $userOptions);
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

        if (!$form instanceof FormInterface) {
            return;
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                if ($sessionBag->has('form_configuration_' . $formId)) {
                    $sessionBag->remove('form_configuration_' . $formId);
                }

                $submissionEvent = new SubmissionEvent($request, $formConfiguration, $form);
                $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_SUBMIT_SUCCESS, $submissionEvent);

                if ($request->isXmlHttpRequest()) {
                    $this->handleAjaxSuccessResponse($event, $submissionEvent, $formId);
                } else {
                    $this->handleDefaultSuccessResponse($event, $submissionEvent, $formId);
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
     * @param                      $formId
     */
    protected function handleDefaultSuccessResponse(GetResponseEvent $event, SubmissionEvent $submissionEvent, $formId)
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
     * @param string           $formId
     */
    protected function handleAjaxSuccessResponse(GetResponseEvent $event, SubmissionEvent $submissionEvent, $formId)
    {
        $redirectUri = null;
        if ($submissionEvent->hasRedirectUri()) {
            $redirectUri = $submissionEvent->getRedirectUri();
        }

        $messages = [];
        $error = false;

        foreach (['success', 'error'] as $type) {
            $messageKey = 'formbuilder_' . $formId . '_' . $type;

            if (!$this->getFlashBag()->has($messageKey)) {
                continue;
            }

            foreach ($this->getFlashBag()->get($messageKey) as $message) {
                if ($type === 'error') {
                    $error = true;
                }
                $messages[] = ['type' => $type, 'message' => $message];
            }
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
            'success'           => false,
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
        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            $generalErrors[] = $error->getMessage();
        }

        if (!empty($generalErrors)) {
            $errors['general'] = $generalErrors;
        }

        foreach ($form->all() as $field) {
            $fieldErrors = [];
            /** @var FormError $error */
            foreach ($field->getErrors() as $error) {
                $fieldErrors[] = $error->getMessage();
            }

            if (!empty($fieldErrors)) {
                $errors[$field->getName()] = $fieldErrors;
            }
        }

        return $errors;
    }

    /**
     * @return FlashBagInterface
     */
    private function getFlashBag()
    {
        return $this->session->getFlashBag();
    }
}
