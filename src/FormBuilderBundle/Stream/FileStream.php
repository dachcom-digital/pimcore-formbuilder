<?php

/**
 * FormBuilder FileHandler.
 * 1. Ensure your php.ini file contains appropriate values for
 *    max_input_time, upload_max_filesize and post_max_size.
 * 2. If you have chunking enabled in Fine Uploader, you MUST set a value for the `chunking.success.endpoint` option.
 *    This will be called by Fine Uploader when all chunks for a file have been successfully uploaded, triggering the
 *    PHP server to combine all parts into one file. This is particularly useful for the concurrent chunking feature,
 *    but is now required in all cases if you are making use of this PHP example.
 */

namespace FormBuilderBundle\Stream;

use FormBuilderBundle\Tool\FileLocator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class FileStream.
 */
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
     * Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp").
     *
     * @var array
     */
    public $allowedExtensions = [];

    /**
     * Specify max file size in bytes.
     *
     * @var null
     */
    public $sizeLimit = null;

    /**
     * matches Fine Uploader's default inputName value by default.
     *
     * @var string
     */
    public $inputName = 'qqfile';

    /**
     * @var string
     */
    protected $uploadName;

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
    public function getName()
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        if ($masterRequest->request->has('qqfilename')) {
            return $masterRequest->request->get('qqfilename', '');
        }

        if ($masterRequest->files->has($this->inputName)) {
            /** @var UploadedFile $file */
            $file = $masterRequest->files->get($this->inputName);

            return $file->getFilename();
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialFiles()
    {
        $initialFiles = [];
        for ($i = 0; $i < 5000; $i++) {
            $fake = ['name' => 'name' . $i, 'uuid' => 'uuid' . $i, 'thumbnailUrl' => 'fu.png'];
            array_push($initialFiles, $fake);
        }

        return $initialFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    /**
     * {@inheritdoc}
     */
    public function getRealFileName()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function combineChunks()
    {
        $tmpDirs = [];
        $chunkSuccess = true;
        $masterRequest = $this->requestStack->getMasterRequest();
        $uuid = $masterRequest->request->get('qquuid');
        $name = preg_replace('/[^a-zA-Z0-9]+/', '', $this->getName());

        $targetPath = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getChunksFolder(), $uuid]);
        $destinationFolderPath = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);
        $destinationPath = join(DIRECTORY_SEPARATOR, [$destinationFolderPath, $name]);

        $totalParts = $masterRequest->request->has('qqtotalparts') ? (int) $masterRequest->request->get('qqtotalparts') : 1;
        $this->uploadName = $name;

        if (!file_exists($destinationPath)) {
            mkdir(dirname($destinationPath), 0777, true);
        }

        $destinationResource = fopen($destinationPath, 'wb');
        if (is_resource($destinationResource)) {
            for ($i = 0; $i < $totalParts; $i++) {
                $chunkPath = $targetPath . DIRECTORY_SEPARATOR . $i;
                $chunkFiles = $this->fileLocator->getFilesFromFolder($chunkPath);
                if ($chunkFiles === null) {
                    $chunkSuccess = false;
                } else {
                    $tmpDirs[] = $chunkPath;
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
            $tmpDirs[] = $destinationPath;
            $tmpDirs[] = $destinationFolderPath;
            $this->cleanUpChunkProcess($tmpDirs);

            return [
                'statusCode'   => 413,
                'success'      => false,
                'uuid'         => $uuid,
                'preventRetry' => true
            ];
        }

        $this->cleanUpChunkProcess($tmpDirs);

        if (!is_null($this->sizeLimit)
            && filesize($destinationPath) > $this->sizeLimit) {
            return [
                'statusCode'   => 413,
                'success'      => false,
                'uuid'         => $uuid,
                'preventRetry' => true
            ];
        }

        return [
            'statusCode' => 200,
            'success'    => true,
            'uuid'       => $uuid
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpload()
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';

            return ['success' => false, 'error' => 'Server error. Increase post_max_size and upload_max_filesize to ' . $neededRequestSize];
        }

        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return ['success' => false, 'error' => 'Server error. Upload directory isn\'t writable'];
        }

        $type = $masterRequest->headers->get('Content-Type');

        if (empty($type)) {
            return ['success' => false, 'error' => 'No files were uploaded.'];
        } elseif (strpos(strtolower($type), 'multipart/') !== 0) {
            return ['success' => false, 'error' => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).'];
        }

        // Get size and name
        /** @var UploadedFile $file */
        $file = $masterRequest->files->get($this->inputName);
        $size = $file->getSize();

        if ($masterRequest->request->has('qqtotalfilesize')) {
            $size = $masterRequest->request->get('qqtotalfilesize');
        }

        //save name!
        $name = preg_replace('/[^a-zA-Z0-9]+/', '', $this->getName());

        // check file error
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload Error #' . $file->getErrorMessage()];
        }

        // Validate name
        if ($name === null || $name === '') {
            return ['success' => false, 'error' => 'File name empty.'];
        }

        // Validate file size
        if ($size == 0) {
            return ['success' => false, 'error' => 'File is empty.'];
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
            return ['success' => false, 'error' => 'File is too large.', 'preventRetry' => true];
        }

        // Validate file extension
        $pathInfo = pathinfo($this->getName());
        $ext = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';

        if (is_array($this->allowedExtensions) &&
            count($this->allowedExtensions) > 0 &&
            !in_array(strtolower($ext), array_map('strtolower', $this->allowedExtensions))
        ) {
            $these = implode(', ', $this->allowedExtensions);

            return ['success' => false, 'error' => 'File has an invalid extension, it should be one of ' . $these . '.'];
        }

        // Save a chunk
        $totalParts = $masterRequest->request->has('qqtotalparts') ? (int) $masterRequest->request->get('qqtotalparts') : 1;
        $uuid = $masterRequest->request->get('qquuid');

        if ($totalParts > 1) {
            // chunked upload
            $chunksFolder = $this->fileLocator->getChunksFolder();
            $partIndex = (int) $masterRequest->request->get('qqpartindex');

            if (!is_writable($chunksFolder) && !is_executable($this->fileLocator->getFilesFolder())) {
                return ['success' => false, 'error' => 'Server error. Chunks directory isn\'t writable or executable.'];
            }

            $targetPath = $this->fileLocator->getChunksFolder() . DIRECTORY_SEPARATOR . $uuid;
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            $target = $targetPath . '/' . $partIndex;
            /** @var UploadedFile $file */
            $file = $masterRequest->files->get($this->inputName);

            try {
                $file->move($target);
            } catch (FileException $e) {
                return [
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'uuid'    => $uuid
                ];
            }

            return [
                'success' => true,
                'error'   => null,
                'uuid'    => $uuid
            ];
        } else {
            // non-chunked upload
            $target = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);

            if ($target) {
                $this->uploadName = basename($target);
                if (!is_dir($target)) {
                    mkdir($target, 0777, true);
                }

                try {
                    $file->move($target);
                } catch (FileException $e) {
                    return [
                        'success' => false,
                        'error'   => $e->getMessage(),
                        'uuid'    => $uuid
                    ];
                }

                return [
                    'success' => true,
                    'uuid'    => $uuid
                ];
            }

            return [
                'success' => false,
                'error'   => 'Could not save uploaded file. The upload was cancelled, or server error encountered'
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleDelete($uuid)
    {
        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return ['error' => 'Server error. Upload directory isn\'t writable' . ((!$this->isWindows()) ? ' or executable.' : '.')];
        }

        $targetPath = $this->fileLocator->getFilesFolder();
        $target = join(DIRECTORY_SEPARATOR, [$targetPath, $uuid]);

        if (is_dir($target)) {
            $this->fileLocator->removeDir($target);

            return [
                'success' => true,
                'uuid'    => $uuid
            ];
        } else {
            return [
                'success' => false,
                'error'   => 'File not found! Unable to delete. UUID: ' . $uuid,
                'path'    => $uuid
            ];
        }
    }

    /**
     * Converts a given size with units to bytes.
     *
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
                $val *= 1024;

                break;
            case 'm':
                $val *= 1024;

                break;
            case 'k':
                $val *= 1024;

                break;
        }

        return $val;
    }

    /**
     * Determines whether a directory can be accessed.
     * is_executable() is not reliable on Windows prior PHP 5.0.0
     *  (http://www.php.net/manual/en/function.is-executable.php)
     * The following tests if the current OS is Windows and if so, merely
     * checks if the folder is writable;
     * otherwise, it checks additionally for executable status (like before).
     *
     * @param string $directory The target directory to test access
     *
     * @return bool
     */
    protected function isInaccessible($directory)
    {
        $isWin = $this->isWindows();
        $folderInaccessible = ($isWin) ? !is_writable($directory) : (!is_writable($directory) && !is_executable($directory));

        return $folderInaccessible;
    }

    /**
     * Determines is the OS is Windows or not.
     *
     * @return bool
     */
    protected function isWindows()
    {
        $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        return $isWin;
    }

    /**
     * @param array $foldersToDelete
     */
    protected function cleanUpChunkProcess(array $foldersToDelete)
    {
        foreach ($foldersToDelete as $folder) {
            $this->fileLocator->removeDir($folder);
        }
    }
}
