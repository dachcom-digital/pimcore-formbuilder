<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Dispatcher;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\SwitchOutputWorkflowData;

class OutputWorkflowResolver implements OutputWorkflowResolverInterface
{
    public function __construct(protected Dispatcher $dispatcher)
    {
    }

    public function resolve(SubmissionEvent $submissionEvent): ?OutputWorkflowInterface
    {
        $form = $submissionEvent->getForm();

        /** @var FormDataInterface $data */
        $data = $form->getData();
        $formDefinition = $data->getFormDefinition();

        $formRuntimeData = $submissionEvent->getFormRuntimeData();
        $userSelectedOutputWorkflow = $formRuntimeData['form_output_workflow'] ?? null;

        /** @var SwitchOutputWorkflowData $switchOutputWorkflowData */
        $switchOutputWorkflowData = $this->checkOutputWorkflowCondition('switch_output_workflow', $data, $formRuntimeData, []);

        if ($switchOutputWorkflowData->hasOutputWorkflowId()) {
            $userSelectedOutputWorkflow = $switchOutputWorkflowData->getOutputWorkflowId();
        }

        $outputWorkflows = $formDefinition->getOutputWorkflows();

        if ($userSelectedOutputWorkflow === null && $outputWorkflows->count() === 1) {
            return $outputWorkflows->first();
        }

        if ($userSelectedOutputWorkflow !== null) {
            $selectedOutputWorkflows = $outputWorkflows->filter(function (OutputWorkflowInterface $outputWorkflow) use ($userSelectedOutputWorkflow) {
                return is_numeric($userSelectedOutputWorkflow)
                    ? $outputWorkflow->getId() === $userSelectedOutputWorkflow
                    : $outputWorkflow->getName() === $userSelectedOutputWorkflow;
            });

            return $selectedOutputWorkflows->count() === 1 ? $selectedOutputWorkflows->first() : null;
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    protected function checkOutputWorkflowCondition(string $dispatchModule, FormDataInterface $formData, array $formRuntimeOptions, array $moduleOptions = []): DataInterface
    {
        return $this->dispatcher->runFormDispatcher($dispatchModule, [
            'formData'           => $formData->getData(),
            'conditionalLogic'   => $formData->getFormDefinition()->getConditionalLogic(),
            'formRuntimeOptions' => $formRuntimeOptions
        ], $moduleOptions);
    }
}
