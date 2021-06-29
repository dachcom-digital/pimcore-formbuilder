<?php

namespace FormBuilderBundle\Controller;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Registry\DynamicMultiFileAdapterRegistry;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends FrontendController
{
    protected Configuration $configuration;
    protected DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry;

    public function __construct(
        Configuration $configuration,
        DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry
    ) {
        $this->configuration = $configuration;
        $this->dynamicMultiFileAdapterRegistry = $dynamicMultiFileAdapterRegistry;
    }

    public function parseAction()
    {
        throw new \RuntimeException('form parse action gets handled by kernel events.');
    }

    public function fileUploadAction(Request $request): Response
    {
        $dmfAdapterName = $this->configuration->getConfig('dynamic_multi_file_adapter');

        try {
            $dmfAdapter = $this->dynamicMultiFileAdapterRegistry->get($dmfAdapterName);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return $dmfAdapter->onUpload($request);
    }

    public function fileDoneAction(Request $request): Response
    {
        $dmfAdapterName = $this->configuration->getConfig('dynamic_multi_file_adapter');

        try {
            $dmfAdapter = $this->dynamicMultiFileAdapterRegistry->get($dmfAdapterName);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return $dmfAdapter->onDone($request);
    }

    public function fileDeleteAction(Request $request, ?string $identifier = null): Response
    {
        $dmfAdapterName = $this->configuration->getConfig('dynamic_multi_file_adapter');

        try {
            $dmfAdapter = $this->dynamicMultiFileAdapterRegistry->get($dmfAdapterName);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return $dmfAdapter->onDelete($request);
    }

    public function getAjaxUrlStructureAction(): JsonResponse
    {
        $router = $this->container->get('router');

        return $this->json([
            'form_parser'     => $router->generate('form_builder.controller.ajax.parse_form'),
            'file_chunk_done' => $router->generate('form_builder.controller.ajax.file_chunk_done'),
            'file_add'        => $router->generate('form_builder.controller.ajax.file_add'),
            'file_delete'     => $router->generate('form_builder.controller.ajax.file_delete'),
        ]);
    }
}
