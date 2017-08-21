<?php

namespace FormBuilderBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends FrontendController
{
    /**
     * @param Request $request
     */
    public function parseAction(Request $request)
    {
        throw new \RuntimeException('form parse action gets handled by kernel events.');
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    public function fileAddAction(Request $request)
    {
        throw new \Exception('not implemented');
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    public function fileDeleteAction(Request $request)
    {
        throw new \Exception('not implemented');
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    public function fileChunkDoneAction(Request $request)
    {
        throw new \Exception('not implemented');
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAjaxUrlStructureAction(Request $request)
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