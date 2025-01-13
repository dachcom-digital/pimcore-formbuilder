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

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Tool\LocaleDataMapper;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\SuccessMessageData;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Snippet;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Contracts\Translation\TranslatorInterface;

class SuccessManagementWorker implements SuccessManagementWorkerInterface
{
    public function __construct(
        protected LocaleDataMapper $localeDataMapper,
        protected IncludeRenderer $includeRenderer,
        protected Dispatcher $dispatcher,
        protected TranslatorInterface $translator
    ) {
    }

    public function process(SubmissionEvent $submissionEvent, array $successManagementConfiguration): void
    {
        $afterSuccess = null;
        $request = $submissionEvent->getRequest();
        $locale = $request->getLocale();

        $form = $submissionEvent->getForm();
        /** @var FormDataInterface $formData */
        $formData = $form->getData();
        $isHeadlessForm = $form->getConfig()->hasOption('is_headless_form') && $form->getConfig()->getOption('is_headless_form') === true;

        $error = false;
        $message = 'Success!';

        /** @var SuccessMessageData $successConditionData */
        $successConditionData = $this->checkSuccessCondition('success_message', $formData, $submissionEvent->getFormRuntimeData());

        if ($successConditionData->hasData()) {
            $afterSuccess = $successConditionData->getIdentifiedData($locale);
            if ($submissionEvent->useFlashBag() === true && $successConditionData->hasFlashMessage()) {
                $submissionEvent->addMessage('redirect_message', $successConditionData->getFlashMessage($locale));
            }
        } else {
            if ($successManagementConfiguration['identifier'] === 'string') {
                $afterSuccess = $this->getString($successManagementConfiguration['value'], $locale);
            } elseif ($successManagementConfiguration['identifier'] === 'snippet') {
                $afterSuccess = $this->getSnippet($successManagementConfiguration['value'], $locale);
            } elseif ($successManagementConfiguration['identifier'] === 'redirect') {
                $afterSuccess = $this->getDocument($successManagementConfiguration['value'], $locale);
            } elseif ($successManagementConfiguration['identifier'] === 'redirect_external') {
                $afterSuccess = $this->getExternalRedirect($successManagementConfiguration['value']);
            }
        }

        $params = [];
        if ($afterSuccess instanceof Document\Snippet) {
            $params['document'] = $afterSuccess;

            try {
                $message = $isHeadlessForm
                    ? [
                        'type' => 'snippet',
                        'data' => $afterSuccess->getId()
                    ]
                    : $this->includeRenderer->render($afterSuccess, $params, false);
            } catch (\Throwable $e) {
                $error = true;
                $message = $isHeadlessForm
                    ? [
                        'type' => 'string',
                        'data' => $e->getMessage()
                    ]
                    : $e->getMessage();
            }
        } elseif ($afterSuccess instanceof Document) {
            $message = null;
            $submissionEvent->setRedirectUri($afterSuccess->getFullPath());
            if (!$submissionEvent->hasMessagesOfType('redirect_message')) {
                $redirectMessage = isset($successManagementConfiguration['flashMessage'])
                    ? $this->getFlashMessage($successManagementConfiguration['flashMessage'], $locale)
                    : null;
                if (!is_null($redirectMessage)) {
                    $submissionEvent->addMessage('redirect_message', $redirectMessage);
                }
            }
        } elseif (is_string($afterSuccess)) {
            // maybe it's an external redirect
            if (str_starts_with($afterSuccess, 'http')) {
                $message = null;
                $submissionEvent->setRedirectUri($afterSuccess);
            } else {
                $message = $isHeadlessForm
                    ? [
                        'type' => 'string',
                        'data' => $afterSuccess
                    ]
                    : $afterSuccess;
            }
        }

        $submissionEvent->addMessage($error ? 'error' : 'success', $message);
    }

    public function getSnippet(array $value, ?string $locale): ?Snippet
    {
        $snippetId = $this->localeDataMapper->mapHref($locale, $value);

        if (is_numeric($snippetId)) {
            return Snippet::getById($snippetId);
        }

        return null;
    }

    public function getString(string $value, ?string $locale): ?string
    {
        return $this->translator->trans((string) $value, [], null, $locale);
    }

    public function getDocument(array $value, ?string $locale): ?Document
    {
        $documentId = $this->localeDataMapper->mapHref($locale, $value);

        if (is_numeric($documentId)) {
            return Document::getById($documentId);
        }

        return null;
    }

    public function getExternalRedirect(string $value): string
    {
        return $value;
    }

    public function getFlashMessage(string $value, ?string $locale): ?string
    {
        return $this->translator->trans((string) $value, [], null, $locale);
    }

    /**
     * @throws \Exception
     */
    protected function checkSuccessCondition(string $dispatchModule, FormDataInterface $formData, array $formRuntimeOptions, array $moduleOptions = []): DataInterface
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'           => $formData->getData(),
            'conditionalLogic'   => $formData->getFormDefinition()->getConditionalLogic(),
            'formRuntimeOptions' => $formRuntimeOptions
        ], $moduleOptions);
    }
}
