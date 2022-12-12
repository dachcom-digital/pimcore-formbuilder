<?php

namespace FormBuilderBundle\Controller\Admin;

use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer\FunnelLayerInterface;
use FormBuilderBundle\Registry\FunnelActionRegistry;
use FormBuilderBundle\Registry\FunnelLayerRegistry;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class OutputWorkflowFunnelController extends AdminController
{
    protected SerializerInterface $serializer;
    protected FunnelLayerRegistry $funnelLayerRegistry;
    protected FunnelActionRegistry $funnelActionRegistry;

    public function __construct(
        SerializerInterface $serializer,
        FunnelLayerRegistry $funnelLayerRegistry,
        FunnelActionRegistry $funnelActionRegistry
    ) {
        $this->serializer = $serializer;
        $this->funnelLayerRegistry = $funnelLayerRegistry;
        $this->funnelActionRegistry = $funnelActionRegistry;
    }

    public function getFunnelLayersAction(Request $request): JsonResponse
    {
        $data = [];
        $services = $this->funnelLayerRegistry->getAll();

        /**
         * @var string               $identifier
         * @var FunnelLayerInterface $service
         */
        foreach ($services as $identifier => $service) {

            $funnelActionDefinitions = $service->getFunnelActionDefinitions();

            if ($service->dynamicFunnelActionAware() === true && count($funnelActionDefinitions) > 0) {
                throw new \Exception('Dynamic action aware funnel is not allowed to provide any preconfigured action elements.');
            }

            $data[] = [
                'label'         => $service->getName(),
                'key'           => $identifier,
                'configuration' => [
                    'dynamicFunnelActionAware' => $service->dynamicFunnelActionAware(),
                    'funnelActionDefinitions'  => $this->serializer instanceof NormalizerInterface
                        ? $this->serializer->normalize($funnelActionDefinitions, 'array', ['groups' => ['ExtJs']])
                        : []
                ]
            ];
        }

        return $this->adminJson([
            'success'      => true,
            'funnelLayers' => $data
        ]);
    }

    public function getFunnelActionsAction(Request $request): JsonResponse
    {
        $data = [];
        $services = $this->funnelActionRegistry->getAll();

        /**
         * @var string                $identifier
         * @var FunnelActionInterface $service
         */
        foreach ($services as $identifier => $service) {
            $data[] = [
                'label' => $service->getName(),
                'key'   => $identifier
            ];
        }

        return $this->adminJson([
            'success'       => true,
            'funnelActions' => $data
        ]);
    }
}
