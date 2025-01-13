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

namespace FormBuilderBundle\Controller;

use FormBuilderBundle\Model\OutputWorkflowInterface;
use FormBuilderBundle\OutputWorkflow\OutputWorkflowDispatcherInterface;
use FormBuilderBundle\Repository\OutputWorkflowRepositoryInterface;
use FormBuilderBundle\Resolver\FunnelDataResolver;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FunnelController extends FrontendController
{
    public function __construct(
        protected OutputWorkflowRepositoryInterface $outputWorkflowRepository,
        protected OutputWorkflowDispatcherInterface $outputWorkflowDispatcher,
        protected FunnelDataResolver $funnelDataResolver,
    ) {
    }

    public function dispatchAction(Request $request, string $funnelId, string $channelId, string $storageToken): Response
    {
        // funnel processing will be handled in FunnelRouteListener

        if (!$this->funnelDataResolver->isFunnelProcessRequest($request)) {
            return $this->renderError($request, 'Funnel Request not found or expired.');
        }

        try {
            $outputWorkflow = $this->outputWorkflowRepository->findById($funnelId);
            if (!$outputWorkflow instanceof OutputWorkflowInterface) {
                return $this->renderError($request, sprintf('Funnel with id %d not found', $funnelId));
            }

            $response = $this->outputWorkflowDispatcher->dispatchOutputWorkflowFunnelProcessing($outputWorkflow, $request);
        } catch (\Throwable $e) {
            return $this->renderError($request, $e->getMessage());
        }

        return $response;
    }

    protected function renderError(Request $request, string $error): Response
    {
        return $request->isXmlHttpRequest()
            ? new JsonResponse(['success' => false, 'message' => $error])
            : $this->renderTemplate('@FormBuilder/funnel/error.html.twig', ['error' => $error]);
    }
}
