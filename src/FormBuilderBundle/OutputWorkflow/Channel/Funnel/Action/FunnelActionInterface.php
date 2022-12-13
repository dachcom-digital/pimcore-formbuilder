<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\Model\OutputWorkflowChannelInterface;

interface FunnelActionInterface
{
    public function getName(): string;

    public function getFormType(): string;

    public function buildFunnelActionElement(
        FunnelActionElement $funnelActionElement,
        OutputWorkflowChannelInterface $channel,
        array $configuration,
        array $context,
    ): FunnelActionElement;
}
