<?php

namespace FormBuilderBundle\EventListener\Funnel;

use FormBuilderBundle\Assembler\FunnelActionElementAssembler;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\FormAssembleEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Model\FormStorageData;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\ReturnToFormAction;
use FormBuilderBundle\Registry\StorageProviderRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FunnelFormListener implements EventSubscriberInterface
{
    protected RequestStack $requestStack;
    protected Configuration $configuration;
    protected StorageProviderRegistry $storageProviderRegistry;

    public function __construct(
        RequestStack $requestStack,
        Configuration $configuration,
        StorageProviderRegistry $storageProviderRegistry
    ) {
        $this->requestStack = $requestStack;
        $this->configuration = $configuration;
        $this->storageProviderRegistry = $storageProviderRegistry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormBuilderEvents::FORM_ASSEMBLE_PRE  => ['formPreAssemble'],
            FormBuilderEvents::FORM_ASSEMBLE_POST => ['formPostAssemble'],
            KernelEvents::FINISH_REQUEST          => ['resetFunnelStorageData'],
        ];
    }

    public function formPreAssemble(FormAssembleEvent $event): void
    {
        if ($this->isFunnelAware() === false) {
            return;
        }

        if (!empty($event->getFormData())) {
            return;
        }

        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return;
        }

        if (
            $request->query->has(ReturnToFormAction::FUNNEL_ACTION_RETURN_TO_FORM_POPULATE) &&
            $request->query->get(ReturnToFormAction::FUNNEL_ACTION_RETURN_TO_FORM_POPULATE) === '0') {
            return;
        }

        $formStorageData = $this->getFunnelStorageData($request, $event->getFormOptionsResolver()->getFormId());
        if (!$formStorageData instanceof FormStorageData) {
            return;
        }

        $event->setFormData($formStorageData->getFormData());
    }

    public function formPostAssemble(FormAssembleEvent $event): void
    {
        if ($this->isFunnelAware() === false) {
            return;
        }

        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return;
        }

        $formStorageData = $this->getFunnelStorageData($request, $event->getFormOptionsResolver()->getFormId());
        if (!$formStorageData instanceof FormStorageData) {
            return;
        }

        if ($request->query->has(FunnelActionElementAssembler::FUNNEL_ERROR_TOKEN_FRAGMENT) === false) {
            return;
        }

        $funnelErrorToken = $request->query->get(FunnelActionElementAssembler::FUNNEL_ERROR_TOKEN_FRAGMENT);

        $errorMessage = $formStorageData->hasFunnelError($funnelErrorToken)
            ? $formStorageData->getFunnelError($funnelErrorToken)
            : null;

        if ($errorMessage !== null && $event->getForm() instanceof FormInterface) {
            $event->getForm()->addError(new FormError($errorMessage));
        }
    }

    public function resetFunnelStorageData(FinishRequestEvent $event): void
    {
        if ($this->isFunnelAware() === false) {
            return;
        }

        $request = $event->getRequest();

        if ($request->query->has(FunnelActionElementAssembler::FUNNEL_FUNNEL_FINISHED_FRAGMENT) === false) {
            return;
        }

        $formId = (int) $request->query->get(FunnelActionElementAssembler::FUNNEL_FUNNEL_FINISHED_FRAGMENT);

        $this->getFunnelStorageData($request, $formId, true);
    }

    private function getFunnelStorageData(Request $request, int $formId, bool $flush = false): ?FormStorageData
    {
        $funnelToken = null;

        if ($request->query->has(FunnelActionElementAssembler::FUNNEL_STORAGE_TOKEN_FRAGMENT)) {
            $funnelToken = $request->query->get(FunnelActionElementAssembler::FUNNEL_STORAGE_TOKEN_FRAGMENT);
        } elseif ($request->attributes->has('_route') && $request->attributes->get('_route') === 'form_builder.controller.funnel.dispatch') {
            $funnelToken = $request->attributes->get('storageToken');
        }

        if ($funnelToken === null) {
            return null;
        }

        $funnelConfiguration = $this->configuration->getConfig('funnel');

        $storageProvider = $this->storageProviderRegistry->get($funnelConfiguration['storage_provider']);
        $data = $storageProvider->fetch($request, $funnelToken);

        if (!$data instanceof FormStorageData) {
            return null;
        }

        if ($data->getFormId() !== $formId) {
            return null;
        }

        if ($flush === true) {
            $storageProvider->flush($request, $funnelToken);
        }

        return $data;
    }

    private function isFunnelAware(): bool
    {
        $funnelConfiguration = $this->configuration->getConfig('funnel');

        return $funnelConfiguration['enabled'] === true;
    }
}
