<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Event\FormAssembleEvent;
use FormBuilderBundle\Form\FormValuesInputApplierInterface;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\ReturnToFormAction;
use FormBuilderBundle\Resolver\FunnelDataResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FunnelFormAssemblingListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected FunnelDataResolver $funnelDataResolver,
        protected FormValuesInputApplierInterface $formValuesInputApplier
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormBuilderEvents::FORM_ASSEMBLE_PRE  => ['formPreAssemble'],
            FormBuilderEvents::FORM_ASSEMBLE_POST => ['formPostAssemble'],
        ];
    }

    public function formPreAssemble(FormAssembleEvent $event): void
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return;
        }

        if ($this->funnelDataResolver->isFunnelShutdownRequest($request) === false) {
            return;
        }

        if (
            !$request->query->has(ReturnToFormAction::FUNNEL_ACTION_RETURN_TO_FORM_POPULATE) ||
            $request->query->get(ReturnToFormAction::FUNNEL_ACTION_RETURN_TO_FORM_POPULATE) === '0'
        ) {
            return;
        }

        if (!empty($event->getFormData())) {
            return;
        }

        $funnelData = $this->funnelDataResolver->getFunnelData($request);
        $formDefinition = $event->getFormDefinition();

        $formData = $this->formValuesInputApplier->apply($funnelData->getFormStorageData()->getFormData(), $formDefinition);

        $event->setFormData($formData);
    }

    public function formPostAssemble(FormAssembleEvent $event): void
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return;
        }

        if ($this->funnelDataResolver->isFunnelErroredRequest($request) === false) {
            return;
        }

        $funnelData = $this->funnelDataResolver->getFunnelData($request);
        $funnelErrorToken = $this->funnelDataResolver->getFunnelErrorToken($request);

        $errorMessage = $funnelData->getFormStorageData()->hasFunnelError($funnelErrorToken)
            ? $funnelData->getFormStorageData()->getFunnelError($funnelErrorToken)
            : null;

        if ($errorMessage !== null && $event->getForm() instanceof FormInterface) {
            $event->getForm()->addError(new FormError($errorMessage));
        }
    }
}
