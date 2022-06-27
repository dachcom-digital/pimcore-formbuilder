<?php

namespace FormBuilderBundle\Stream;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Pimcore\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class FileStream implements FileStreamInterface
{
    public array $allowedExtensions = [];
    public ?int $sizeLimit = null;

    public function __construct(
        protected RequestStack $requestStack,
        protected FilesystemOperator $formbuilderChunkStorage,
        protected FilesystemOperator $formbuilderFilesStorage,
    )
    {
    }

    public function handleUpload(array $options = [], bool $instantChunkCombining = true): array
    {
        $binaryIdentifier = $options['binary'];

        $mainRequest = $this->requestStack->getMainRequest();

        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';

            return [
                'success' => false,
                'error'   => 'Server error. Increase post_max_size and upload_max_filesize to ' . $neededRequestSize
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
                'error'    => 'Upload Error #' . $file->getErrorMessage()
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

        if (is_array($this->allowedExtensions) &&
            count($this->allowedExtensions) > 0 &&
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
                $this->formbuilderChunkStorage->write(sprintf('%s%s%s', $uuid, DIRECTORY_SEPARATOR, $partIndex), file_get_contents($file->getPathname()));
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
            $this->formbuilderFilesStorage->write($uuid . '/' . $serverFileSafeName, file_get_contents($file->getPathname()));
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

        $uuid = $mainRequest->request->get($options['uuid']);
        $fileSafeName = $this->getSafeFileName($options['fileName']);
        $totalParts = $mainRequest->request->has($options['totalChunkCount']) ? (int) $mainRequest->request->get($options['totalChunkCount']) : 1;

        $tmpStream = tmpfile();

        for ($i = 0; $i < $totalParts; $i++) {
            $chunkFiles = $this->formbuilderChunkStorage->listContents($uuid);

            foreach ($chunkFiles as $chunkFile) {
                $chunkPathResource = $this->formbuilderChunkStorage->readStream($chunkFile->path());
                stream_copy_to_stream($chunkPathResource, $tmpStream);
                fclose($chunkPathResource);
            }
        }

        try {
            $this->formbuilderFilesStorage->writeStream($uuid . '/' . $fileSafeName, $tmpStream);
        }
        catch (FilesystemException $exception) {
            $chunkSuccess = false;
        }

        // Success
        fclose($tmpStream);

        $tmpDirs[] = $uuid;

        if ($chunkSuccess === false) {
            try {
                $this->formbuilderChunkStorage->deleteDirectory($uuid);
            }
            catch (FilesystemException $exception) {
                //Ignore
            }

            try {
                $this->formbuilderFilesStorage->deleteDirectory($uuid);
            }
            catch (FilesystemException $exception) {
                //Ignore
            }

            return [
                'statusCode'   => 413,
                'success'      => false,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        $this->deleteDirectories($tmpDirs);

        $fileSize = $this->formbuilderFilesStorage->fileSize($uuid . '/' . $fileSafeName);

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
        if ($checkChunkFolder === true) {
            if ($this->formbuilderChunkStorage->directoryExists($identifier)) {
                $this->formbuilderChunkStorage->deleteDirectory($identifier);
            }
        }

        if ($this->formbuilderFilesStorage->directoryExists($identifier)) {
            $this->formbuilderFilesStorage->deleteDirectory($identifier);
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

    protected function isInaccessible(string $directory): bool
    {
        return $this->isWindows() ? !is_writable($directory) : (!is_writable($directory) && !is_executable($directory));
    }

    protected function isWindows(): bool
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    protected function getSafeFileName(string $fileName, bool $strong = false): string
    {
        if ($strong === false) {
            return File::getValidFilename($fileName);
        }

        return preg_replace('/[^a-zA-Z0-9]_+/', '', str_replace('.', '_', $fileName));
    }
}
