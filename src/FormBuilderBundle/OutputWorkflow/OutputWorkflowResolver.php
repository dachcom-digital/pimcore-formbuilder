<?php

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\OutputWorkflowInterface;

class OutputWorkflowResolver implements OutputWorkflowResolverInterface
{
    public function resolve(SubmissionEvent $submissionEvent): ?OutputWorkflowInterface
    {
        $form = $submissionEvent->getForm();

        /** @var FormDataInterface $data */
        $data = $form->getData();
        $formDefinition = $data->getFormDefinition();

        $formRuntimeData = $submissionEvent->getFormRuntimeData();
        $userSelectedOutputWorkflow = $formRuntimeData['form_output_workflow'] ?? null;

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
}
