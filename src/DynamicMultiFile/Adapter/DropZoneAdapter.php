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

namespace FormBuilderBundle\DynamicMultiFile\Adapter;

use FormBuilderBundle\Form\Type\DynamicMultiFile\DropZoneType;
use FormBuilderBundle\Stream\FileStreamInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DropZoneAdapter implements DynamicMultiFileAdapterInterface
{
    protected FileStreamInterface $fileStream;

    public function __construct(FileStreamInterface $fileStream)
    {
        $this->fileStream = $fileStream;
    }

    public function getForm(): string
    {
        return DropZoneType::class;
    }

    public function getJsHandler(): string
    {
        return 'drop-zone';
    }

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

    public function onDone(Request $request): Response
    {
        return new JsonResponse([
            'success' => false,
            'message' => 'not implemented'
        ], 403);
    }

    public function onDelete(Request $request): Response
    {
        $identifier = $request->attributes->get('identifier');
        $checkChunkFolder = $request->request->get('uploadStatus') === 'canceled';

        $result = $this->fileStream->handleDelete($identifier, $checkChunkFolder);

        return new JsonResponse($result);
    }
}
