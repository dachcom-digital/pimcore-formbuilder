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

namespace FormBuilderBundle\OutputWorkflow;

use FormBuilderBundle\Event\SubmissionEvent;
use FormBuilderBundle\Model\OutputWorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FunnelWorkerInterface
{
    /**
     * @throws \Exception
     */
    public function initiateFunnel(OutputWorkflowInterface $outputWorkflow, SubmissionEvent $submissionEvent): void;

    /**
     * @throws \Exception
     */
    public function processFunnel(OutputWorkflowInterface $outputWorkflow, Request $request): Response;
}
