<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Model\FunnelActionElement;

interface FunnelLayerInterface
{
    public function getName(): string;

    public function getFormType(): array;

    /**
     * @return array<int, FunnelActionElement>
     */
    public function getFunnelActions(): array;
}
