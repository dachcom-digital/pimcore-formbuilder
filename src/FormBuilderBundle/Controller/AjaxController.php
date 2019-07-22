<?php

namespace FormBuilderBundle\Controller;

use FormBuilderBundle\Stream\FileStreamInterface;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;

class AjaxController extends FrontendController
{
    /**
     * @var FileStreamInterface
     */
    protected $fileStream;

    /**
     * @param FileStreamInterface $fileStream
     */
    public function __construct(FileStreamInterface $fileStream)
    {
        $this->fileStream = $fileStream;
    }

    /**
     * @throws \RuntimeException
     */
    public function parseAction()
    {
        throw new \RuntimeException('form parse action gets handled by kernel events.');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function fileAddAction(Request $request)
    {
        $method = $request->getMethod();

        $formId = $request->request->get('formId');
        $fieldName = $request->request->get('fieldName');

        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->container->get('session')->getBag('form_builder_session');

        if ($method === 'POST') {
            $result = $this->fileStream->handleUpload();
            $result['uploadName'] = $this->fileStream->getRealFileName();

            if ($result['success'] === true) {
                $sessionKey = 'file_' . $formId . '_' . $result['uuid'];
                $sessionValue = ['fileName' => $result['uploadName'], 'fieldName' => $fieldName, 'uuid' => $result['uuid']];
                $sessionBag->set($sessionKey, $sessionValue);
            }

            return $this->json($result);
        } elseif ($method === 'DELETE') {
            return $this->fileDeleteAction($request, $request->request->get('uuid'));
        } else {
            $response = new Response();
            $response->headers->set('Content-Type', 'text/plain');
            $response->headers->set('Cache-Control', 'no-cache');
            $response->setStatusCode(405);

            return $response;
        }
    }

    /**
     * @param Request $request
     * @param string  $uuid
     *
     * @return JsonResponse
     */
    public function fileDeleteAction(Request $request, $uuid = '')
    {
        $formId = $request->request->get('formId');

        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->container->get('session')->getBag('form_builder_session');

        //remove tmp element from session!
        $sessionKey = 'file_' . $formId . '_' . $uuid;
        $sessionBag->remove($sessionKey);

        $result = $this->fileStream->handleDelete($uuid);

        return $this->json($result);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fileChunkDoneAction(Request $request)
    {
        $formId = $request->request->get('formId');
        $fieldName = $request->request->get('fieldName');

        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->container->get('session')->getBag('form_builder_session');

        $result = $this->fileStream->combineChunks();

        // To return a name used for uploaded file you can use the following line.
        $result['uploadName'] = $this->fileStream->getRealFileName();

        if ($result['success'] === true) {
            //add uuid to session to find it again later!
            $sessionKey = 'file_' . $formId . '_' . $result['uuid'];
            $sessionValue = ['fileName' => $result['uploadName'], 'fieldName' => $fieldName, 'uuid' => $result['uuid']];
            $sessionBag->set($sessionKey, $sessionValue);
        }

        return $this->json($result, $result['statusCode']);
    }

    /**
     * @return JsonResponse
     */
    public function getAjaxUrlStructureAction()
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
