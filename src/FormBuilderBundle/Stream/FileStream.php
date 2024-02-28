<?php

namespace FormBuilderBundle\Stream;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Pimcore\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FileStream implements FileStreamInterface
{
    public array $allowedExtensions = [];
    public ?int $sizeLimit = null;

    public function __construct(
        protected RequestStack $requestStack,
        protected FilesystemOperator $formBuilderChunkStorage,
        protected FilesystemOperator $formBuilderFilesStorage
    ) {
    }

    public function handleUpload(array $options = [], bool $instantChunkCombining = true): array
    {
        $binaryIdentifier = $options['binary'];

        $mainRequest = $this->requestStack->getMainRequest();

        if (!$mainRequest instanceof Request) {
            return [
                'success' => false,
                'error'   => 'No request given'
            ];
        }

        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';

            return [
                'success' => false,
                'error'   => sprintf('Server error. Increase post_max_size and upload_max_filesize to %s', $neededRequestSize)
            ];
        }

        $type = $mainRequest->headers->get('Content-Type');

        if (empty($type)) {
            return [
                'success' => false,
                'error'   => 'No files were uploaded.'
            ];
        }

        if (!str_starts_with(strtolower($type), 'multipart/')) {
            return [
                'success' => false,
                'error'   => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).'
            ];
        }

        /** @var UploadedFile $file */
        $file = $mainRequest->files->get($binaryIdentifier);

        $size = $file->getSize();
        $fileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();

        if ($mainRequest->request->has($options['totalFileSize'])) {
            $size = $mainRequest->request->get($options['totalFileSize']);
        }

        $serverFileSafeName = $this->getSafeFileName($fileName, true);
        $fileSafeName = $this->getSafeFileName($fileName);

        // check file error
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => sprintf('Upload Error: %s', $file->getErrorMessage())
            ];
        }

        // Validate name
        if ($fileSafeName === '') {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => 'File name empty.'
            ];
        }

        // Validate file size
        if ($size === 0 || $size === '0') {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => 'File is empty.'
            ];
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
            return [
                'success'      => false,
                'fileName'     => $fileSafeName,
                'error'        => 'File is too large.',
                'preventRetry' => true
            ];
        }

        if (count($this->allowedExtensions) > 0 &&
            !in_array(strtolower($fileExtension), array_map('strtolower', $this->allowedExtensions), true)) {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => sprintf('File has an invalid extension, it should be one of %s.', implode(', ', $this->allowedExtensions))
            ];
        }

        $totalParts = $mainRequest->request->has($options['totalChunkCount']) ? (int) $mainRequest->request->get($options['totalChunkCount']) : 1;
        $uuid = $mainRequest->request->get($options['uuid']);

        if ($totalParts > 1) {

            // chunked upload
            $partIndex = (int) $mainRequest->request->get($options['chunkIndex']);

            try {
                $this->formBuilderChunkStorage->write(sprintf('%s%s%s', $uuid, DIRECTORY_SEPARATOR, $partIndex), file_get_contents($file->getPathname()));
            } catch (FilesystemException $e) {
                return [
                    'success'  => false,
                    'fileName' => $fileSafeName,
                    'error'    => $e->getMessage(),
                    'uuid'     => $uuid
                ];
            }

            if ($instantChunkCombining === true && ($partIndex + 1) === $totalParts) {
                return $this->combineChunks(array_merge($options, ['fileName' => $fileSafeName]));
            }

            return [
                'success'  => true,
                'fileName' => $fileSafeName,
                'error'    => null,
                'uuid'     => $uuid
            ];
        }

        try {
            $this->formBuilderFilesStorage->write($uuid . '/' . $serverFileSafeName, file_get_contents($file->getPathname()));
        } catch (FilesystemException $e) {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => $e->getMessage(),
                'uuid'     => $uuid
            ];
        }

        return [
            'success'  => true,
            'fileName' => $fileSafeName,
            'uuid'     => $uuid
        ];

    }

    public function combineChunks(array $options = []): array
    {
        $chunkSuccess = true;
        $mainRequest = $this->requestStack->getMainRequest();

        if (!$mainRequest instanceof Request) {
            return [
                'statusCode'   => 400,
                'success'      => false,
            ];
        }

        $uuid = $mainRequest->request->get($options['uuid']);
        $fileSafeName = $this->getSafeFileName($options['fileName']);

        $tmpStream = tmpfile();
        $chunkFiles = $this->formBuilderChunkStorage->listContents($uuid)->toArray();

        usort($chunkFiles, static function (StorageAttributes $a, StorageAttributes $b) {

            $pathInfoA = pathinfo($a->path());
            $pathInfoB = pathinfo($b->path());

            return $pathInfoA['filename'] <=> $pathInfoB['filename'];
        });

        foreach ($chunkFiles as $chunkFile) {
            $chunkPathResource = $this->formBuilderChunkStorage->readStream($chunkFile->path());
            stream_copy_to_stream($chunkPathResource, $tmpStream);
            fclose($chunkPathResource);
        }

        try {
            $this->formBuilderFilesStorage->writeStream($uuid . '/' . $fileSafeName, $tmpStream);
        } catch (FilesystemException $exception) {
            $chunkSuccess = false;
        }

        // Success
        fclose($tmpStream);

        if ($chunkSuccess === false) {

            try {
                $this->formBuilderChunkStorage->deleteDirectory($uuid);
            } catch (FilesystemException $exception) {
                // fail silently
            }

            try {
                $this->formBuilderFilesStorage->deleteDirectory($uuid);
            } catch (FilesystemException $exception) {
                // fail silently
            }

            return [
                'statusCode'   => 413,
                'success'      => false,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        try {
            $this->formBuilderChunkStorage->deleteDirectory($uuid);
        } catch (FilesystemException $exception) {
            // fail silently
        }

        $fileSize = $this->formBuilderFilesStorage->fileSize($uuid . '/' . $fileSafeName);

        if (!is_null($this->sizeLimit) && $fileSize > $this->sizeLimit) {
            return [
                'statusCode'   => 413,
                'success'      => false,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        return [
            'statusCode' => 200,
            'success'    => true,
            'uuid'       => $uuid,
            'fileName'   => $fileSafeName,
        ];
    }

    public function handleDelete(string $identifier, bool $checkChunkFolder = false): array
    {
        if ($checkChunkFolder === true && $this->formBuilderChunkStorage->directoryExists($identifier)) {
            $this->formBuilderChunkStorage->deleteDirectory($identifier);
        }

        if ($this->formBuilderFilesStorage->directoryExists($identifier)) {
            $this->formBuilderFilesStorage->deleteDirectory($identifier);
        }

        return [
            'success' => true,
            'uuid'    => $identifier
        ];
    }

    protected function toBytes(mixed $sizeStr): int|string
    {
        $val = trim($sizeStr);
        if (is_numeric($val)) {
            return $val;
        }

        $last = strtolower($sizeStr[strlen($sizeStr) - 1]);
        $val = (int) substr($val, 0, -1);

        if (!in_array($last, ['g', 'm', 'k'], true)) {
            return $val;
        }

        return $val * 1024;
    }

    protected function getSafeFileName(string $fileName, bool $strong = false): string
    {
        if ($strong === false) {
            return File::getValidFilename($fileName);
        }

        return preg_replace('/[^a-zA-Z0-9]_+/', '', str_replace('.', '_', $fileName));
    }
}
