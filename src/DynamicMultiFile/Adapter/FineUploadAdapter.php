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

use FormBuilderBundle\Form\Type\DynamicMultiFile\FineUploaderType;
use FormBuilderBundle\Stream\FileStreamInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FineUploadAdapter implements DynamicMultiFileAdapterInterface
{
    protected FileStreamInterface $fileStream;

    public function __construct(FileStreamInterface $fileStream)
    {
        $this->fileStream = $fileStream;
    }

    public function getForm(): string
    {
        return FineUploaderType::class;
    }

    public function getJsHandler(): string
    {
        return 'fine-uploader';
    }

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
        }

        if ($method === 'DELETE') {
            return $this->onDelete($request);
        }

        return new JsonResponse([], 405);
    }

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

    public function onDelete(Request $request): Response
    {
        $identifier = $request->attributes->has('identifier')
            ? $request->attributes->get('identifier')
            : $request->request->get('uuid');

        $result = $this->fileStream->handleDelete($identifier);

        return new JsonResponse($result);
    }
}
