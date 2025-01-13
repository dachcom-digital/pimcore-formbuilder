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

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Registry\DynamicMultiFileAdapterRegistry;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends FrontendController
{
    public function __construct(
        protected Configuration $configuration,
        protected DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function parseAction(): void
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
