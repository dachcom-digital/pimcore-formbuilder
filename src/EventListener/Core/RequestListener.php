<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Builder\FrontendFormBuilder;
use FormBuilderBundle\Event\DoubleOptInSubmissionEvent;
use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Manager\FormDefinitionManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\OutputWorkflow\FormSubmissionFinisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RequestListener implements EventSubscriberInterface
{
    private const FORM_TYPE_DEFAULT = 'formbuilder_';
    private const FORM_TYPE_DOUBLE_OPT_IN = 'formbuilder_double_opt_in_';

    public function __construct(
        protected FrontendFormBuilder $frontendFormBuilder,
        protected EventDispatcherInterface $eventDispatcher,
        protected FormSubmissionFinisherInterface $formSubmissionFinisher,
        protected FormDefinitionManager $formDefinitionManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $form = null;
        $formRuntimeData = null;

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        [$formId, $formType] = $this->findFormIdByRequest($request);

        if ($formId === null) {
            return;
        }

        $formDefinition = $this->formDefinitionManager->getById($formId);
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return;
        }

        try {
            if ($formType === self::FORM_TYPE_DOUBLE_OPT_IN) {
                $form = $this->frontendFormBuilder->buildDoubleOptInForm($formDefinition);
            } elseif ($formType === self::FORM_TYPE_DEFAULT) {
                $formRuntimeData = $this->detectFormRuntimeDataInRequest($event->getRequest(), $formDefinition);
                if ($formRuntimeData !== null) {
                    $form = $this->frontendFormBuilder->buildForm($formDefinition, $formRuntimeData);
                }
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid form type "%s"', $formType));
            }
        } catch (\Throwable $e) {
            $this->generateErroredJsonReturn($event, $e);

            return;
        }

        if (!$form instanceof FormInterface) {
            return;
        }

        if (!$form->isSubmitted()) {
            return;
        }

        if ($form->isValid() === false) {
            $this->doneWithError($event, $form);

            return;
        }

        if ($formType === self::FORM_TYPE_DOUBLE_OPT_IN) {
            $this->doubleOptInDoneWithSuccess($event, $formDefinition, $form);

            return;
        }

        $this->doneWithSuccess($event, $form, $formRuntimeData);
    }

    protected function doneWithError(RequestEvent $event, FormInterface $form): void
    {
        $request = $event->getRequest();
        $finishResponse = $this->formSubmissionFinisher->finishWithError($request, $form);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    protected function doubleOptInDoneWithSuccess(RequestEvent $event, FormDefinitionInterface $formDefinition, FormInterface $form): void
    {
        $request = $event->getRequest();
        $submissionEvent = new DoubleOptInSubmissionEvent($request, $formDefinition, $form);
        $this->eventDispatcher->dispatch($submissionEvent, FormBuilderEvents::FORM_DOUBLE_OPT_IN_SUBMIT_SUCCESS);

        $finishResponse = $this->formSubmissionFinisher->finishDoubleOptInWithSuccess($request, $submissionEvent);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    protected function doneWithSuccess(RequestEvent $event, FormInterface $form, ?array $formRuntimeData): void
    {
        $request = $event->getRequest();
        $submissionEvent = new SubmissionEvent($request, $formRuntimeData, $form);
        $this->eventDispatcher->dispatch($submissionEvent, FormBuilderEvents::FORM_SUBMIT_SUCCESS);

        $finishResponse = $this->formSubmissionFinisher->finishWithSuccess($request, $submissionEvent);

        if ($finishResponse instanceof Response) {
            $event->setResponse($finishResponse);
        }
    }

    protected function generateErroredJsonReturn(RequestEvent $event, ?\Throwable $e, ?string $message = null): void
    {
        $request = $event->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return;
        }

        $response = new JsonResponse([
            'success' => false,
            'error'   => $e instanceof \Throwable ? $e->getMessage() : $message,
            'trace'   => $e instanceof \Throwable ? $e->getTrace() : [],
        ]);

        $event->setResponse($response);
    }

    protected function findFormIdByRequest(Request $request): ?array
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
            return [null, null];
        }

        if (empty($data)) {
            return [null, null];
        }

        foreach ([self::FORM_TYPE_DOUBLE_OPT_IN, self::FORM_TYPE_DEFAULT] as $formType) {
            if (null !== $formId = $this->detectFormIdByType($data, $formType)) {
                return [$formId, $formType];
            }
        }

        return [null, null];
    }

    protected function detectFormRuntimeDataInRequest(Request $request, FormDefinitionInterface $formDefinition): ?array
    {
        $formDefinitionConfig = $formDefinition->getConfiguration();

        $data = null;
        $name = sprintf('formbuilder_%s', $formDefinition->getId());
        $method = isset($formDefinitionConfig['method']) ? strtoupper($formDefinitionConfig['method']) : 'POST';

        if ($request->getMethod() !== $method) {
            return [];
        }

        if (in_array($method, ['GET', 'HEAD', 'TRACE']) && $request->query->has($name)) {
            $data = $request->query->all($name);
        } elseif ($request->request->has($name)) {
            $data = $request->request->all($name);
        }

        if (!is_array($data)) {
            return null;
        }

        if (isset($data['formRuntimeData']) && is_string($data['formRuntimeData'])) {
            return json_decode($data['formRuntimeData'], true, 512, JSON_THROW_ON_ERROR);
        }

        return null;
    }

    protected function detectFormIdByType(array $values, string $formMatchType = self::FORM_TYPE_DEFAULT): ?int
    {
        foreach ($values as $key => $parameters) {
            if (!str_contains($key, $formMatchType)) {
                continue;
            }

            if (isset($parameters['formId'])) {
                return $parameters['formId'];
            }
        }

        return null;
    }
}
