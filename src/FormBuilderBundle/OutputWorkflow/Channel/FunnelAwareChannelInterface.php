<?php

namespace FormBuilderBundle\OutputWorkflow\Channel;

use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerInterface;
use FormBuilderBundle\OutputWorkflow\FunnelWorkerData;
use Symfony\Component\HttpFoundation\Response;

interface FunnelAwareChannelInterface
{
    /**
     * @throws \Exception
     */
    public function dispatchFunnelProcessing(FunnelWorkerData $funnelWorkerData): Response;

    /**
     * @throws \Exception
     */
    public function getFunnelLayer(array $funnelConfiguration): FunnelLayerInterface;
}
