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
use Pimcore\Logger;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class FileStream
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
     * Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
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
     * matches Fine Uploader's default inputName value by default
     *
     * @var string
     */
    public $inputName = 'qqfile';

    /**
     * @var
     */
    protected $uploadName;

    /**
     * PackageStream constructor.
     *
     * @param FileLocator  $fileLocator
     * @param RequestStack $requestStack
     */
    public function __construct(FileLocator $fileLocator, RequestStack $requestStack)
    {
        $this->fileLocator = $fileLocator;
        $this->requestStack = $requestStack;
    }

    /**
     * Get the original filename
     */
    public function getName()
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        if ($masterRequest->request->has('qqfilename')) {
            return $masterRequest->request->get('qqfilename');
        }

        if ($masterRequest->files->has($this->inputName)) {
            /** @var UploadedFile $file */
            $file = $masterRequest->files->get($this->inputName);

            return $file->getFilename();
        }

        return false;
    }

    /**
     * @return array
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
     * Get the name of the uploaded file
     *
     * @return mixed
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    /**
     * Get the real name of the uploaded file
     *
     * @return bool|mixed|string
     */
    public function getRealFileName()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    public function combineChunks()
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        $uuid = $masterRequest->request->get('qquuid');

        $name = preg_replace('/[^a-zA-Z0-9]+/', '', $this->getName());

        $targetFolder = $this->fileLocator->getChunksFolder() . DIRECTORY_SEPARATOR . $uuid;
        $totalParts = $masterRequest->request->has('qqtotalparts') ? (int)$masterRequest->request->get('qqtotalparts') : 1;
        $targetPath = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid, $name]);
        $this->uploadName = $name;

        if (!file_exists($targetPath)) {
            mkdir(dirname($targetPath), 0777, true);
        }

        $target = fopen($targetPath, 'wb');
        for ($i = 0; $i < $totalParts; $i++) {
            $chunk = fopen($targetFolder . DIRECTORY_SEPARATOR . $i, 'rb');
            stream_copy_to_stream($chunk, $target);
            fclose($chunk);
        }

        // Success
        fclose($target);
        for ($i = 0; $i < $totalParts; $i++) {
            $this->fileLocator->removeDir($targetFolder . DIRECTORY_SEPARATOR . $i);
        }

        rmdir($targetFolder);

        if (!is_null($this->sizeLimit) && filesize($targetPath) > $this->sizeLimit) {
            $this->fileLocator->removeDir($targetPath);

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
     * @return array
     */
    public function handleUpload()
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return ['error' => 'Server error. Increase post_max_size and upload_max_filesize to ' . $neededRequestSize];
        }

        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return ['error' => 'Server error. Upload directory isn\'t writable'];
        }

        $type = $masterRequest->headers->get('Content-Type');

        if (empty($type)) {
            return ['error' => 'No files were uploaded.'];
        } elseif (strpos(strtolower($type), 'multipart/') !== 0) {
            return ['error' => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).'];
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
            return ['error' => 'Upload Error #' . $file->getErrorMessage()];
        }

        // Validate name
        if ($name === null || $name === '') {
            return ['error' => 'File name empty.'];
        }

        // Validate file size
        if ($size == 0) {
            return ['error' => 'File is empty.'];
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit) {
            return ['error' => 'File is too large.', 'preventRetry' => true];
        }

        // Validate file extension
        $pathInfo = pathinfo($this->getName());
        $ext = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';

        if ($this->allowedExtensions && !in_array(strtolower($ext), array_map('strtolower', $this->allowedExtensions))) {
            $these = implode(', ', $this->allowedExtensions);
            return ['error' => 'File has an invalid extension, it should be one of ' . $these . '.'];
        }

        // Save a chunk
        $totalParts = $masterRequest->request->has('qqtotalparts') ? (int)$masterRequest->request->get('qqtotalparts') : 1;
        $uuid = $masterRequest->request->get('qquuid');

        if ($totalParts > 1) {
            # chunked upload
            $chunksFolder = $this->fileLocator->getChunksFolder();
            $partIndex = (int)$masterRequest->request->get('qqpartindex');

            if (!is_writable($chunksFolder) && !is_executable($this->fileLocator->getFilesFolder())) {
                return ['error' => 'Server error. Chunks directory isn\'t writable or executable.'];
            }

            $targetFolder = $this->fileLocator->getChunksFolder() . DIRECTORY_SEPARATOR . $uuid;
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }

            $target = $targetFolder . '/' . $partIndex;
            /** @var UploadedFile $file */
            $file = $masterRequest->files->get($this->inputName);
            $success = $file->move($target);
            return [
                'success' => $success,
                'uuid'    => $uuid
            ];
        } else {
            # non-chunked upload
            $target = join(DIRECTORY_SEPARATOR, [$this->fileLocator->getFilesFolder(), $uuid]);

            if ($target) {
                $this->uploadName = basename($target);
                if (!is_dir($target)) {
                    mkdir($target, 0777, true);
                }

                if ($file->move($target)) {
                    return [
                        'success' => true,
                        'uuid'    => $uuid
                    ];
                }
            }

            return ['error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered'];
        }
    }

    /**
     * @param $uuid
     *
     * @return array
     */
    public function handleDelete($uuid)
    {
        if ($this->isInaccessible($this->fileLocator->getFilesFolder())) {
            return ['error' => 'Server error. Upload directory isn\'t writable' . ((!$this->isWindows()) ? ' or executable.' : '.')];
        }

        $targetFolder = $this->fileLocator->getFilesFolder();
        $target = join(DIRECTORY_SEPARATOR, [$targetFolder, $uuid]);

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
     * @param $data
     * @param $formName
     * @param $templateId
     * @return bool|null|Asset
     * @throws \Exception
     */
    public function createZipAsset($data, $formName, $templateId)
    {
        if (!is_array($data)) {
            return false;
        }

        $files = [];

        //Find all Files!
        foreach ($data as $folderName => $fileName) {
            $fileDir = $this->fileLocator->getFilesFolder() . '/' . $folderName;
            if (is_dir($fileDir)) {
                $dirFiles = glob($fileDir . '/*');
                if (count($dirFiles) === 1) {
                    $files[] = ['name' => $fileName, 'uuid' => $folderName, 'path' => $dirFiles[0]];
                }
            }
        }

        if (empty($files)) {
            return false;
        }

        $zipFileName = uniqid('form-') . '.zip';
        $zipPath = $this->fileLocator->getZipFolder() . '/' . $zipFileName;

        try {
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $fileInfo) {
                $zip->addFile($fileInfo['path'], $fileInfo['name']);
            }

            $zip->close();

            //clean up!
            foreach ($files as $fileInfo) {
                $this->handleDelete($fileInfo['uuid']);
            }
        } catch (\Exception $e) {
            Logger::log('Error while creating zip for FormBuilder (' . $zipPath . '): ' . $e->getMessage());
            return false;
        }

        if (!file_exists($zipPath)) {
            Logger::log('Zip Path does not exist (' . $zipPath . ')');
            return false;
        }

        $formDataFolder = null;
        $formDataParentFolder = Asset\Folder::getByPath('/formdata');

        if (!$formDataParentFolder instanceof Asset\Folder) {
            Logger::error('formDataParent Folder does not exist (/formdata)!');
            return false;
        }

        $formName = \Pimcore\File::getValidFilename($formName);
        $formFolderExists = Asset\Service::pathExists('/formdata/' . $formName);

        if ($formFolderExists === false) {
            $formDataFolder = new Asset\Folder();
            $formDataFolder->setCreationDate(time());
            $formDataFolder->setLocked(true);
            $formDataFolder->setUserOwner(1);
            $formDataFolder->setUserModification(0);
            $formDataFolder->setParentId($formDataParentFolder->getId());
            $formDataFolder->setFilename($formName);
            $formDataFolder->save();
        } else {
            $formDataFolder = Asset\Folder::getByPath('/formdata/' . $formName);
        }

        if (!$formDataFolder instanceof Asset\Folder) {
            Logger::error('Error while creating formDataFolder: (/formdata/' . $formName . ')');
            return false;
        }

        $assetData = [
            'data'     => file_get_contents($zipPath),
            'filename' => $zipFileName
        ];

        $asset = null;
        try {
            $mailTemplate = \Pimcore\Model\Document::getById($templateId);
            $asset = Asset::create($formDataFolder->getId(), $assetData, false);
            $asset->setProperty('linkedForm', 'document', $mailTemplate);
            $asset->save();

            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        } catch (\Exception $e) {
            Logger::log('Error while storing asset in Pimcore (' . $zipPath . '): ' . $e->getMessage());

            return false;
        }

        return $asset;
    }

    /**
     * Converts a given size with units to bytes.
     *
     * @param string $str
     *
     * @return int|string
     */
    protected function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);

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
     * Determines is the OS is Windows or not
     *
     * @return boolean
     */
    protected function isWindows()
    {
        $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        return $isWin;
    }
}