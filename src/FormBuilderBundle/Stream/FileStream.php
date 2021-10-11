<?php

namespace FormBuilderBundle\Stream;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class FileStream implements FileStreamInterface
{
    protected FileLocator $fileLocator;
    protected RequestStack $requestStack;
    public array $allowedExtensions = [];
    public ?int $sizeLimit = null;

    public function __construct(FileLocator $fileLocator, RequestStack $requestStack)
    {
        $this->fileLocator = $fileLocator;
        $this->requestStack = $requestStack;
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

        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return [
                'success' => false,
                'error'   => 'Server error. Upload directory isn\'t writable'
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
            $chunksFolder = $this->fileLocator->getChunksFolder();
            $partIndex = (int) $mainRequest->request->get($options['chunkIndex']);

            if (!is_writable($chunksFolder) && !is_executable($this->fileLocator->getFilesFolder())) {
                return [
                    'success'  => false,
                    'fileName' => $fileSafeName,
                    'error'    => 'Server error. Chunks directory isn\'t writable or executable.'
                ];
            }

            $targetPath = $this->fileLocator->getChunksFolder() . DIRECTORY_SEPARATOR . $uuid;

            try {
                $this->fileLocator->assertDir($targetPath);
            } catch (\Throwable $e) {
                return [
                    'success'  => false,
                    'fileName' => $fileSafeName,
                    'error'    => sprintf('Could not create directory: %s', $e->getMessage()),
                    'uuid'     => $uuid
                ];
            }

            try {
                $file->move(sprintf('%s%s%s', $targetPath, DIRECTORY_SEPARATOR, $partIndex));
            } catch (FileException $e) {
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

        // non-chunked upload
        $target = implode(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);

        try {
            $this->fileLocator->assertDir($target);
        } catch (\Throwable $e) {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => sprintf('Could not create directory: %s', $e->getMessage()),
                'uuid'     => $uuid
            ];
        }

        try {
            $file->move($target, $serverFileSafeName);
        } catch (FileException $e) {
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
        $tmpDirs = [];
        $chunkSuccess = true;
        $mainRequest = $this->requestStack->getMainRequest();

        $uuid = $mainRequest->request->get($options['uuid']);
        $serverFileSafeName = $this->getSafeFileName($options['fileName'], true);
        $fileSafeName = $this->getSafeFileName($options['fileName']);

        $targetPath = implode(DIRECTORY_SEPARATOR, [$this->fileLocator->getChunksFolder(), $uuid]);
        $destinationFolderPath = implode(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);
        $destinationPath = implode(DIRECTORY_SEPARATOR, [$destinationFolderPath, $serverFileSafeName]);

        $totalParts = $mainRequest->request->has($options['totalChunkCount']) ? (int) $mainRequest->request->get($options['totalChunkCount']) : 1;

        try {
            $this->fileLocator->assertDir(dirname($destinationPath));
        } catch (\Throwable $e) {
            return [
                'statusCode'   => 413,
                'success'      => false,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName
            ];
        }

        $destinationResource = fopen($destinationPath, 'wb');
        if (is_resource($destinationResource)) {
            for ($i = 0; $i < $totalParts; $i++) {
                $chunkPath = $targetPath . DIRECTORY_SEPARATOR . $i;
                $chunkFiles = $this->fileLocator->getFilesFromFolder($chunkPath);
                if ($chunkFiles === null) {
                    $chunkSuccess = false;
                } else {
                    foreach ($chunkFiles as $chunkFile) {
                        $chunkPathResource = fopen($chunkFile->getPathname(), 'rb');
                        if (!is_resource($chunkPathResource)) {
                            $chunkSuccess = false;

                            continue;
                        }

                        stream_copy_to_stream($chunkPathResource, $destinationResource);
                        fclose($chunkPathResource);
                    }
                }
            }

            // Success
            fclose($destinationResource);
        } else {
            $chunkSuccess = false;
        }

        $tmpDirs[] = $targetPath;

        if ($chunkSuccess === false) {

            $tmpDirs[] = $destinationFolderPath;
            $this->deleteDirectories($tmpDirs);

            return [
                'statusCode'   => 413,
                'success'      => false,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        $this->deleteDirectories($tmpDirs);

        if (!is_null($this->sizeLimit) && filesize($destinationPath) > $this->sizeLimit) {
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
        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return ['error' => 'Server error. Upload directory isn\'t writable' . ((!$this->isWindows()) ? ' or executable.' : '.')];
        }

        $targetPath = $this->fileLocator->getFilesFolder();
        $target = implode(DIRECTORY_SEPARATOR, [$targetPath, $identifier]);

        if ($checkChunkFolder === true) {
            $chunkPath = implode(DIRECTORY_SEPARATOR, [$this->fileLocator->getChunksFolder(), $identifier]);

            if (is_dir($chunkPath)) {
                $this->deleteDirectories([$chunkPath]);
            }
        }

        if (is_dir($target)) {
            $this->deleteDirectories([$target]);
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

        $val *= match ($last) {
            'g', 'm', 'k' => 1024,
        };

        return $val;
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

    protected function deleteDirectories(array $foldersToDelete): void
    {
        foreach ($foldersToDelete as $folder) {
            $this->fileLocator->removeDir($folder);
        }
    }
}
