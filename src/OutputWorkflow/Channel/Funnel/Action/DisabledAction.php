<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type\DisabledActionType;
use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;

class DisabledAction implements FunnelActionInterface
{
    public function getName(): string
    {
        return 'Disable Action';
    }

    public function getFormType(): string
    {
        return DisabledActionType::class;
    }

    public function buildFunnelActionElement(
        FunnelActionElement $funnelActionElement,
        OutputWorkflowChannelInterface $channel,
        array $configuration,
        array $context,
    ): FunnelActionElement {

        $funnelActionElement->setPath('#');
        $funnelActionElement->setDisabled(true);

        return $funnelActionElement;
    }
}
