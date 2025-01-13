<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
