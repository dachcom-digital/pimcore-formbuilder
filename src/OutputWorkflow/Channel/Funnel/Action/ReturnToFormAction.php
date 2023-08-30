<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type\ReturnToFormActionType;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;

class ReturnToFormAction implements FunnelActionInterface
{
    public const FUNNEL_ACTION_RETURN_TO_FORM_POPULATE = 'funnel_populate_form';

    public function getName(): string
    {
        return 'Return To Form';
    }

    public function getFormType(): string
    {
        return ReturnToFormActionType::class;
    }

    public function buildFunnelActionElement(
        FunnelActionElement $funnelActionElement,
        OutputWorkflowChannelInterface $channel,
        array $configuration,
        array $context,
    ): FunnelActionElement {

        $initiationPath = $context['initiationPath'];
        $populateForm = $configuration['populateForm'] ?? false;

        if ($initiationPath === null) {

            $funnelActionElement->setPath('#');

            return $funnelActionElement;
        }

        $query = [
            self::FUNNEL_ACTION_RETURN_TO_FORM_POPULATE  => $populateForm,
        ];

        $funnelActionElement->setPath(sprintf('%s?%s', $initiationPath, http_build_query($query)));

        return $funnelActionElement;
    }
}
