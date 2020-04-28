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
use Pimcore\Translation\Translator;

class SuccessManagementWorker implements SuccessManagementWorkerInterface
{
    /**
     * @var LocaleDataMapper
     */
    protected $localeDataMapper;

    /**
     * @var FlashBagManagerInterface
     */
    protected $flashBagManager;

    /**
     * @var IncludeRenderer
     */
    protected $includeRenderer;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param LocaleDataMapper         $localeDataMapper
     * @param FlashBagManagerInterface $flashBagManager
     * @param IncludeRenderer          $includeRenderer
     * @param Dispatcher               $dispatcher
     * @param Translator               $translator
     */
    public function __construct(
        LocaleDataMapper $localeDataMapper,
        FlashBagManagerInterface $flashBagManager,
        IncludeRenderer $includeRenderer,
        Dispatcher $dispatcher,
        Translator $translator
    ) {
        $this->localeDataMapper = $localeDataMapper;
        $this->flashBagManager = $flashBagManager;
        $this->includeRenderer = $includeRenderer;
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(SubmissionEvent $submissionEvent, array $successManagementConfiguration)
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
            if (substr($afterSuccess, 0, 4) === 'http') {
                $submissionEvent->setRedirectUri($afterSuccess);
            } else {
                $message = $afterSuccess;
            }
        }

        $key = sprintf('formbuilder_%s_%s', $formId, ($error ? 'error' : 'success'));

        $this->flashBagManager->add($key, $message);
    }

    /**
     * @param array  $value
     * @param string $locale
     *
     * @return Snippet|null
     */
    public function getSnippet($value, $locale)
    {
        $snippetId = $this->localeDataMapper->mapHref($locale, $value);

        if (is_numeric($snippetId)) {
            return Snippet::getById($snippetId);
        }

        return null;
    }

    /**
     * @param string $value
     * @param string $locale
     *
     * @return null|string
     */
    public function getString($value, $locale)
    {
        return $this->translator->trans((string) $value, [], null, $locale);
    }

    /**
     * @param array  $value
     * @param string $locale
     *
     * @return Document|null
     */
    public function getDocument($value, $locale)
    {
        $documentId = $this->localeDataMapper->mapHref($locale, $value);

        if (is_numeric($documentId)) {
            return Document::getById($documentId);
        }

        return null;
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function getExternalRedirect($value)
    {
        return $value;
    }

    /**
     * @param string $value
     * @param string $locale
     *
     * @return null|string
     */
    public function getFlashMessage($value, $locale)
    {
        return $this->translator->trans((string) $value, [], null, $locale);
    }

    /**
     *
     * @param string            $dispatchModule
     * @param FormDataInterface $formData
     * @param array             $formRuntimeOptions
     * @param array             $moduleOptions
     *
     * @return DataInterface
     *
     * @throws \Exception
     */
    protected function checkSuccessCondition(string $dispatchModule, FormDataInterface $formData, array $formRuntimeOptions, $moduleOptions = [])
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'           => $formData->getData(),
            'conditionalLogic'   => $formData->getFormDefinition()->getConditionalLogic(),
            'formRuntimeOptions' => $formRuntimeOptions
        ], $moduleOptions);
    }
}
