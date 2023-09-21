<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Session\FlashBagManagerInterface;
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
        protected FlashBagManagerInterface $flashBagManager,
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

        $formId = $formData->getFormDefinition()->getId();
        $error = false;
        $message = 'Success!';

        /** @var SuccessMessageData $successConditionData */
        $successConditionData = $this->checkSuccessCondition('success_message', $formData, $submissionEvent->getFormRuntimeData());

        if ($successConditionData->hasData()) {
            $afterSuccess = $successConditionData->getIdentifiedData($locale);
            if ($successConditionData->hasFlashMessage()) {
                $this->flashBagManager->add('formbuilder_redirect_flash_message', $successConditionData->getFlashMessage($locale));
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
                $message = $this->includeRenderer->render($afterSuccess, $params, false);
            } catch (\Exception $e) {
                $error = true;
                $message = $e->getMessage();
            }
        } elseif ($afterSuccess instanceof Document) {
            $message = $afterSuccess->getFullPath();
            $submissionEvent->setRedirectUri($afterSuccess->getFullPath());
            if (!$this->flashBagManager->has('formbuilder_redirect_flash_message')) {
                $redirectFlashMessage = isset($successManagementConfiguration['flashMessage'])
                    ? $this->getFlashMessage($successManagementConfiguration['flashMessage'], $locale)
                    : null;
                if (!is_null($redirectFlashMessage)) {
                    $this->flashBagManager->add('formbuilder_redirect_flash_message', $redirectFlashMessage);
                }
            }
        } elseif (is_string($afterSuccess)) {
            // maybe it's an external redirect
            if (str_starts_with($afterSuccess, 'http')) {
                $submissionEvent->setRedirectUri($afterSuccess);
            } else {
                $message = $afterSuccess;
            }
        }

        $key = sprintf('formbuilder_%s_%s', $formId, ($error ? 'error' : 'success'));

        $this->flashBagManager->add($key, $message);
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
