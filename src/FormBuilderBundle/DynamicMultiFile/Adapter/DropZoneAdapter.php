<?php

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

use FormBuilderBundle\Form\Type\DynamicMultiFile\DropZoneType;
use FormBuilderBundle\Stream\FileStreamInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DropZoneAdapter implements DynamicMultiFileAdapterInterface
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
        return DropZoneType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getJsHandler(): string
    {
        return 'drop-zone';
    }

    /**
     * {@inheritDoc}
     */
    public function onUpload(Request $request): Response
    {
        $result = $this->fileStream->handleUpload([
            'binary'          => 'dmfData',
            'uuid'            => 'uuid',
            'chunkIndex'      => 'dzchunkindex',
            'totalChunkCount' => 'dztotalchunkcount',
            'totalFileSize'   => 'dztotalfilesize',
        ]);

        return new JsonResponse($result);

    }

    /**
     * {@inheritDoc}
     */
    public function onDone(Request $request): Response
    {
        return new JsonResponse([
            'success' => false,
            'message' => 'not implemented'
        ], 403);
    }

    /**
     * {@inheritDoc}
     */
    public function onDelete(Request $request): Response
    {
        $identifier = $request->attributes->get('identifier');
        $checkChunkFolder = $request->request->get('uploadStatus') === 'canceled';

        $result = $this->fileStream->handleDelete($identifier, $checkChunkFolder);

        return new JsonResponse($result);
    }
}
