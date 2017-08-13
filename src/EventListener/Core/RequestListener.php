<?php


namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Builder;
use FormBuilderBundle\FormBuilderEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @var Session
     */
    protected $session;

    /**
     * RequestListener constructor.
     *
     * @param Builder $formBuilder
     * @param EventDispatcherInterface $eventDispatcher
     * @param Session $session
     */
    public function __construct(
        Builder $formBuilder,
        EventDispatcherInterface $eventDispatcher,
        Session $session
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
            KernelEvents::REQUEST   => ['onKernelRequest'],
        ];
    }

    /**
     *
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
            list($formId, $form) =  $this->formBuilder->buildByRequest($request);

            if (!$form instanceof FormInterface) {
                return;
            }

        } catch (\Exception $e) {
            return;
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
            $sessionBag = $this->session->getBag('form_builder_session');

            $formConfiguration = [];
            if($sessionBag->has('form_configuration_' . $formId)) {
                $formConfiguration = $sessionBag->get('form_configuration_' . $formId);
                $sessionBag->remove('form_configuration_' . $formId);
            }

            $submissionEvent = new SubmissionEvent($request, $formConfiguration, $form);
            $this->eventDispatcher->dispatch(FormBuilderEvents::FORM_SUBMIT_SUCCESS, $submissionEvent);

            $response = new RedirectResponse('?send=true');
            $event->setResponse($response);
        }
    }
}
