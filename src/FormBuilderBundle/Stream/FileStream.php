<?php

namespace FormBuilderBundle\Stream;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class FileStream implements FileStreamInterface
{
    /**
     * @var FileLocator
     */
    protected $fileLocator;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var array
     */
    public $allowedExtensions = [];

    /**
     * @var null
     */
    public $sizeLimit = null;

    /**
     * @param FileLocator  $fileLocator
     * @param RequestStack $requestStack
     */
    public function __construct(FileLocator $fileLocator, RequestStack $requestStack)
    {
        $this->fileLocator = $fileLocator;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpload(array $options = [], bool $instantChunkCombining = true)
    {
        $binaryIdentifier = $options['binary'];

        $masterRequest = $this->requestStack->getMasterRequest();

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

        $type = $masterRequest->headers->get('Content-Type');

        if (empty($type)) {
            return [
                'success' => false,
                'error'   => 'No files were uploaded.'
            ];
        }

        if (strpos(strtolower($type), 'multipart/') !== 0) {
            return [
                'success' => false,
                'error'   => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).'
            ];
        }

        /** @var UploadedFile $file */
        $file = $masterRequest->files->get($binaryIdentifier);

        $size = $file->getSize();
        $fileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();

        if ($masterRequest->request->has($options['totalFileSize'])) {
            $size = $masterRequest->request->get($options['totalFileSize']);
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
        if ($fileSafeName === null || $fileSafeName === '') {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => 'File name empty.'
            ];
        }

        // Validate file size
        if ($size == 0) {
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

        if (is_array($this->allowedExtensions) && count($this->allowedExtensions) > 0 && !in_array(strtolower($fileExtension),
                array_map('strtolower', $this->allowedExtensions))) {
            return [
                'success'  => false,
                'fileName' => $fileSafeName,
                'error'    => sprintf('File has an invalid extension, it should be one of %s.', implode(', ', $this->allowedExtensions))
            ];
        }

        $totalParts = $masterRequest->request->has($options['totalChunkCount']) ? (int) $masterRequest->request->get($options['totalChunkCount']) : 1;
        $uuid = $masterRequest->request->get($options['uuid']);

        if ($totalParts > 1) {

            // chunked upload
            $chunksFolder = $this->fileLocator->getChunksFolder();
            $partIndex = (int) $masterRequest->request->get($options['chunkIndex']);

            if (!is_writable($chunksFolder) && !is_executable($this->fileLocator->getFilesFolder())) {
                return [
                    'success'  => false,
                    'fileName' => $fileSafeName,
                    'error'    => 'Server error. Chunks directory isn\'t writable or executable.'
                ];
            }

            $targetPath = $this->fileLocator->getChunksFolder() . DIRECTORY_SEPARATOR . $uuid;
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
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
        $target = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);

        if (!is_dir($target)) {
            mkdir($target, 0755, true);
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

    /**
     * {@inheritdoc}
     */
    public function combineChunks(array $options = [])
    {
        $tmpDirs = [];
        $chunkSuccess = true;
        $masterRequest = $this->requestStack->getMasterRequest();

        $uuid = $masterRequest->request->get($options['uuid']);
        $serverFileSafeName = $this->getSafeFileName($options['fileName'], true);
        $fileSafeName = $this->getSafeFileName($options['fileName']);

        $targetPath = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getChunksFolder(), $uuid]);
        $destinationFolderPath = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);
        $destinationPath = join(DIRECTORY_SEPARATOR, [$destinationFolderPath, $serverFileSafeName]);

        $totalParts = $masterRequest->request->has($options['totalChunkCount']) ? (int) $masterRequest->request->get($options['totalChunkCount']) : 1;

        if (!file_exists($destinationPath)) {
            mkdir(dirname($destinationPath), 0755, true);
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
            $this->deleteFolders($tmpDirs);

            return [
                'statusCode'   => 413,
                'success'      => false,
                'preventRetry' => true,
                'uuid'         => $uuid,
                'fileName'     => $fileSafeName,
            ];
        }

        $this->deleteFolders($tmpDirs);

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

    /**
     * {@inheritdoc}
     */
    public function handleDelete($uuid, bool $checkChunkFolder = false)
    {
        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return ['error' => 'Server error. Upload directory isn\'t writable' . ((!$this->isWindows()) ? ' or executable.' : '.')];
        }

        $targetPath = $this->fileLocator->getFilesFolder();
        $target = join(DIRECTORY_SEPARATOR, [$targetPath, $uuid]);

        if ($checkChunkFolder === true) {
            $chunkPath = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getChunksFolder(), $uuid]);

            if (is_dir($chunkPath)) {
                $this->deleteFolders([$chunkPath]);
            }
        }

        if (is_dir($target)) {
            $this->deleteFolders([$target]);
        }

        return [
            'success' => true,
            'uuid'    => $uuid
        ];

    }

    /**
     * @param string $sizeStr
     *
     * @return int|string
     */
    protected function toBytes($sizeStr)
    {
        $val = trim($sizeStr);
        if (is_numeric($val)) {
            return $val;
        }

        $last = strtolower($sizeStr[strlen($sizeStr) - 1]);
        $val = (int) substr($val, 0, -1);

        switch ($last) {
            case 'g':
            case 'm':
            case 'k':
                $val *= 1024;

                break;
        }

        return $val;
    }

    /**
     * @return bool
     */
    protected function isInaccessible($directory)
    {
        return $this->isWindows() ? !is_writable($directory) : (!is_writable($directory) && !is_executable($directory));
    }

    /**
     * @return bool
     */
    protected function isWindows()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * @param string $fileName
     * @param bool   $strong
     *
     * @return string
     */
    protected function getSafeFileName(string $fileName, bool $strong = false)
    {
        if ($strong === false) {
            return File::getValidFilename($fileName);
        }

        return preg_replace('/[^a-zA-Z0-9]_+/', '', str_replace('.', '_', $fileName));
    }

    /**
     * @param array $foldersToDelete
     */
    protected function deleteFolders(array $foldersToDelete)
    {
        foreach ($foldersToDelete as $folder) {
            $this->fileLocator->removeDir($folder);
        }
    }
}
