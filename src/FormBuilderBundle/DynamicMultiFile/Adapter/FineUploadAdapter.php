<?php

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

use FormBuilderBundle\Form\Type\DynamicMultiFile\FineUploaderType;
use FormBuilderBundle\Stream\FileStreamInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FineUploadAdapter implements DynamicMultiFileAdapterInterface
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
     * {@inheritDoc}
     */
    public function getForm(): string
    {
        return FineUploaderType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getJsHandler(): string
    {
        return 'fine-uploader';
    }

    /**
     * {@inheritDoc}
     */
    public function onUpload(Request $request): Response
    {
        $method = $request->getMethod();

        if ($method === 'POST') {

            $result = $this->fileStream->handleUpload([
                'binary'          => 'qqfile',
                'uuid'            => 'qquuid',
                'chunkIndex'      => 'qqpartindex',
                'totalChunkCount' => 'qqtotalparts',
                'totalFileSize'   => 'qqtotalfilesize',
            ], false);

            return new JsonResponse($result);

        } elseif ($method === 'DELETE') {
            return $this->onDelete($request);
        }

        return new JsonResponse([], 405);
    }

    /**
     * {@inheritDoc}
     */
    public function onDone(Request $request): Response
    {
        $result = $this->fileStream->combineChunks([
            'fileName'        => $request->request->get('qqfilename'),
            'uuid'            => 'qquuid',
            'chunkIndex'      => 'qqpartindex',
            'totalChunkCount' => 'qqtotalparts',
            'totalFileSize'   => 'qqtotalfilesize',
        ]);

        return new JsonResponse($result, $result['statusCode']);
    }

    /**
     * {@inheritDoc}
     */
    public function onDelete(Request $request): Response
    {
        $identifier = $request->attributes->has('identifier')
            ? $request->attributes->get('identifier')
            : $request->request->get('uuid');

        $result = $this->fileStream->handleDelete($identifier);

        return new JsonResponse($result);
    }
}
