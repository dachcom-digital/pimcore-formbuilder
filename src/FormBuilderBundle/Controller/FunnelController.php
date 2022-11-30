<?php

namespace FormBuilderBundle\Controller;

use FormBuilderBundle\OutputWorkflow\FunnelWorkerInterface;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FunnelController extends FrontendController
{
    protected FunnelWorkerInterface $funnelWorker;

    public function __construct(FunnelWorkerInterface $funnelWorker)
    {
        $this->funnelWorker = $funnelWorker;
    }

    public function dispatchAction(Request $request, string $funnelId, string $channelId, string $storageToken): Response
    {
        try {
            $response = $this->funnelWorker->processFunnel($request, $funnelId, $channelId, $storageToken);
        } catch (\Throwable $e) {

            return $request->isXmlHttpRequest()
                ? new JsonResponse(['success' => false, 'message' => $e->getMessage()])
                : $this->renderTemplate('@FormBuilder/funnel/error.html.twig', ['error' => $e->getMessage()]);
        }

        return $response;
    }
}
