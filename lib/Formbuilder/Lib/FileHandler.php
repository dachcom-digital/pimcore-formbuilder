<?php

/**
 *
 * Formbuilder FileHandler.
 *
 * 1. Ensure your php.ini file contains appropriate values for
 *    max_input_time, upload_max_filesize and post_max_size.
 *
 * 2. If you have chunking enabled in Fine Uploader, you MUST set a value for the `chunking.success.endpoint` option.
 *    This will be called by Fine Uploader when all chunks for a file have been successfully uploaded, triggering the
 *    PHP server to combine all parts into one file. This is particularly useful for the concurrent chunking feature,
 *    but is now required in all cases if you are making use of this PHP example.
 */

namespace Formbuilder\Lib;

use \Pimcore\Model\Asset;

class FileHandler {

    /**
     * @var null
     */
    private $tmpFolder = NULL;

    /**
     *  If you want to use the chunking/resume feature, specify the folder to temporarily save parts.
     * @var string
     */
    private $chunksFolder = 'chunks';

    /**
     * @var string
     */
    private $filesFolder = 'files';

    /**
     * @var string
     */
    private $zipFolder = 'zip';

    /**
     * Specify the list of valid extensions, ex. array("jpeg", "xml", "bmp")
     * @var array
     */
    public $allowedExtensions = array();

    /**
     * Specify max file size in bytes.
     * @var null
     */
    public $sizeLimit = NULL;

    /**
     * matches Fine Uploader's default inputName value by default
     * @var string
     */
    public $inputName = 'qqfile';


    /**
     * @var float
     */
    public $chunksCleanupProbability = 0.001; // Once in 1000 requests on avg

    /**
     * @var int
     */
    public $chunksExpireIn = 604800; // One wee

    /**
     * @var
     */
    protected $uploadName;

    public function __construct()
    {
        $this->setupTmpFolder();
    }

    /**
     * Get the original filename
     */
    public function getName()
    {
        if (isset($_REQUEST['qqfilename']))
        {
            return $_REQUEST['qqfilename'];
        }

        if (isset($_FILES[$this->inputName]))
        {
            return $_FILES[$this->inputName]['name'];
        }

        return FALSE;
    }

    public function getInitialFiles()
    {
        $initialFiles = array();
        for ($i = 0; $i < 5000; $i++)
        {
            $fake = array('name' => 'name' . $i, 'uuid' => 'uuid' . $i, 'thumbnailUrl' => 'fu.png');
            array_push($initialFiles, $fake);
        }

        return $initialFiles;
    }

    /**
     * Get the name of the uploaded file
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    /**
     * Get the real name of the uploaded file
     */
    public function getRealFileName()
    {
        return $this->getName();
    }

    public function combineChunks()
    {
        $uuid = $_POST['qquuid'];

        $name = preg_replace('/[^a-zA-Z0-9]+/', '', $this->getName());

        $targetFolder = $this->chunksFolder . DIRECTORY_SEPARATOR . $uuid;
        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int) $_REQUEST['qqtotalparts'] : 1;
        $targetPath = join(DIRECTORY_SEPARATOR, array($this->filesFolder, $uuid, $name));
        $this->uploadName = $name;

        if ( !file_exists($targetPath) )
        {
            mkdir(dirname($targetPath), 0777, TRUE);
        }

        $target = fopen($targetPath, 'wb');
        for ($i=0; $i<$totalParts; $i++)
        {
            $chunk = fopen($targetFolder . DIRECTORY_SEPARATOR . $i, 'rb');
            stream_copy_to_stream($chunk, $target);
            fclose($chunk);
        }

        // Success
        fclose($target);
        for ($i=0; $i<$totalParts; $i++)
        {
            unlink($targetFolder . DIRECTORY_SEPARATOR . $i);
        }

        rmdir($targetFolder);

        if (!is_null($this->sizeLimit) && filesize($targetPath) > $this->sizeLimit)
        {
            unlink($targetPath);
            http_response_code(413);
            
            return array(
                'success'       => FALSE,
                'uuid'          => $uuid,
                'preventRetry'  => TRUE
            );
        }

        return array(
            'success'   => TRUE,
            'uuid'      => $uuid
        );
    }

    /**
     * Process the upload.
     *
     * @return array
     */
    public function handleUpload()
    {
        if (is_writable($this->chunksFolder) && 1 == mt_rand(1, 1/$this->chunksCleanupProbability))
        {
            // Run garbage collection
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit || $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit)
        {
            $neededRequestSize = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return array( 'error' => 'Server error. Increase post_max_size and upload_max_filesize to ' . $neededRequestSize);
        }

        if ( $this->isInaccessible( $this->filesFolder ) )
        {
            return array('error' => 'Server error. Uploads directory isn\'t writable');
        }

        $type = $_SERVER['CONTENT_TYPE'];
        if (isset($_SERVER['HTTP_CONTENT_TYPE']))
        {
            $type = $_SERVER['HTTP_CONTENT_TYPE'];
        }

        if( !isset($type) )
        {
            return array('error' => 'No files were uploaded.');
        }
        else if (strpos(strtolower($type), 'multipart/') !== 0)
        {
            return array('error' => 'Server error. Not a multipart request. Please set forceMultipart to default value (true).');
        }

        // Get size and name
        $file = $_FILES[$this->inputName];
        $size = $file['size'];

        if (isset($_REQUEST['qqtotalfilesize']))
        {
            $size = $_REQUEST['qqtotalfilesize'];
        }

        //save name!
        $name = preg_replace('/[^a-zA-Z0-9]+/', '', $this->getName());

        // check file error
        if($file['error'])
        {
            return array('error' => 'Upload Error #' . $file['error']);
        }

        // Validate name
        if ($name === NULL || $name === '')
        {
            return array('error' => 'File name empty.');
        }

        // Validate file size
        if ($size == 0)
        {
            return array('error' => 'File is empty.');
        }

        if (!is_null($this->sizeLimit) && $size > $this->sizeLimit)
        {
            return array('error' => 'File is too large.', 'preventRetry' => TRUE);
        }

        // Validate file extension
        $pathinfo = pathinfo($this->getName());
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        if($this->allowedExtensions && !in_array(strtolower($ext), array_map('strtolower', $this->allowedExtensions)))
        {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }

        // Save a chunk
        $totalParts = isset($_REQUEST['qqtotalparts']) ? (int) $_REQUEST['qqtotalparts'] : 1;
        $uuid = $_REQUEST['qquuid'];

        if ($totalParts > 1)
        {
            # chunked upload
            $chunksFolder = $this->chunksFolder;
            $partIndex = (int)$_REQUEST['qqpartindex'];

            if (!is_writable($chunksFolder) && !is_executable($this->filesFolder))
            {
                return array('error' => 'Server error. Chunks directory isn\'t writable or executable.');
            }

            $targetFolder = $this->chunksFolder . DIRECTORY_SEPARATOR . $uuid;
            if (!file_exists($targetFolder))
            {
                mkdir($targetFolder, 0777, TRUE);
            }

            $target = $targetFolder . '/' . $partIndex;
            $success = move_uploaded_file($_FILES[$this->inputName]['tmp_name'], $target);

            return array(

                'success'   => $success,
                'uuid'      => $uuid

            );
        }
        else
        {
            # non-chunked upload
            $target = join(DIRECTORY_SEPARATOR, array($this->filesFolder, $uuid, $name));
            if ($target)
            {
                $this->uploadName = basename($target);
                if (!is_dir(dirname($target)))
                {
                    mkdir(dirname($target), 0777, TRUE);
                }

                if (move_uploaded_file($file['tmp_name'], $target))
                {
                    return array(

                        'success'   => TRUE,
                        'uuid'      => $uuid

                    );
                }
            }

            return array('error'=> 'Could not save uploaded file.' . 'The upload was cancelled, or server error encountered');
        }

    }

    /**
     * Process a delete.
     * @params string $name Overwrites the name of the file.
     *
     * @return array
     */
    public function handleDelete( $uuid )
    {
        if ($this->isInaccessible( $this->filesFolder ))
        {
            return array('error' => 'Server error. Uploads directory isn\'t writable' . ((!$this->isWindows()) ? ' or executable.' : '.'));
        }

        $targetFolder = $this->filesFolder;
        $target = join(DIRECTORY_SEPARATOR, array($targetFolder, $uuid));

        if (is_dir($target))
        {
            $this->removeDir($target);

            return array(

                'success'   => TRUE,
                'uuid'      => $uuid

            );
        }
        else
        {
            return array(

                'success'   => FALSE,
                'error'     => 'File not found! Unable to delete. UUID: ' . $uuid,
                'path'      => $uuid

            );
        }
    }

    /**
     * @param $data
     * @param string $formName
     * @param int $templateId
     *
     * @return bool|null|Asset
     */
    public function createZipAsset( $data, $formName, $templateId)
    {
        if( !is_array( $data ) )
        {
            return FALSE;
        }

        $files = array();

        //Find all Files!
        foreach( $data as $folderName => $fileName )
        {
            $fileDir = $this->filesFolder . '/' . $folderName;
            if( is_dir( $fileDir ) )
            {
                $dirFiles = glob($fileDir . '/*');

                if( count( $dirFiles ) === 1 )
                {
                    $files[] = array('name' => $fileName, 'uuid' => $folderName, 'path' => $dirFiles[0] );
                }

            }

        }

        if( empty( $files ) )
        {
            return FALSE;
        }

        $zipFileName = uniqid('form-') . '.zip';
        $zipPath = $this->zipFolder . '/' . $zipFileName;

        try
        {
            $zip = new \ZipArchive();
            $zip->open( $zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $fileInfo)
            {
                $zip->addFile($fileInfo['path'], $fileInfo['name']);
            }

            $zip->close();

            //clean up!
            foreach ($files as $fileInfo)
            {
                $this->handleDelete( $fileInfo['uuid'] );
            }
        }
        catch( \Exception $e )
        {
            \Pimcore\Logger::log('Error while creating zip for Formbuilder (' . $zipPath . '): ' . $e->getMessage());
            return FALSE;
        }

        if( !file_exists( $zipPath ) )
        {
            \Pimcore\Logger::log('Zip Path does not exist (' . $zipPath . ')');
            return FALSE;
        }

        $formDataFolder = NULL;
        $formDataParentFolder = Asset\Folder::getByPath( '/formdata' );

        if( !$formDataParentFolder instanceof Asset\Folder)
        {
            \Pimcore\Logger::error('formDataParent Folder does not exist (/formdata)!');
            return FALSE;
        }

        $formName = \Pimcore\File::getValidFilename( $formName );
        $formFolderExists = Asset\Service::pathExists( '/formdata/' . $formName );

        if( $formFolderExists === FALSE )
        {
            $formDataFolder = new Asset\Folder();
            $formDataFolder->setCreationDate ( time() );
            $formDataFolder->setLocked(true);
            $formDataFolder->setUserOwner (1);
            $formDataFolder->setUserModification (0);
            $formDataFolder->setParentId($formDataParentFolder->getId());
            $formDataFolder->setFilename($formName);
            $formDataFolder->save();
        }
        else
        {
            $formDataFolder = Asset\Folder::getByPath( '/formdata/' . $formName );
        }

        if( !$formDataFolder instanceof Asset\Folder)
        {
            \Pimcore\Logger::error('Error while creating formDataFolder: (/formdata/' . $formName . ')');
            return FALSE;
        }

        $assetData = array(

            'data'      => file_get_contents( $zipPath ),
            'filename'  => $zipFileName

        );

        $asset = NULL;

        try
        {
            $mailTemplate = \Pimcore\Model\Document::getById( $templateId );

            $asset = \Pimcore\Model\Asset::create( $formDataFolder->getId(), $assetData, FALSE );
            $asset->setProperty('linkedForm', 'document', $mailTemplate );
            $asset->save();

            if( file_exists( $zipPath ) )
            {
                unlink( $zipPath );
            }

        }
        catch( \Exception $e )
        {
            \Pimcore\Logger::log('Error while storing asset in Pimcore (' . $zipPath . '): ' . $e->getMessage());
            return FALSE;
        }

        return $asset;

    }

    /**
     * Returns a path to use with this upload. Check that the name does not exist,
     * and appends a suffix otherwise.
     * @param string $uploadDirectory Target directory
     * @param string $filename The name of the file to use.
     *
     * @return bool|string
     */
    protected function getUniqueTargetPath($uploadDirectory, $filename)
    {
        // Allow only one process at the time to get a unique file name, otherwise
        // if multiple people would upload a file with the same name at the same time
        // only the latest would be saved.
        if (function_exists('sem_acquire'))
        {
            $lock = sem_get( ftok(__FILE__, 'u') );
            sem_acquire($lock);
        }

        $pathinfo = pathinfo($filename);
        $base = $pathinfo['filename'];
        $ext = isset( $pathinfo['extension'] ) ? $pathinfo['extension'] : '';
        $ext = $ext == '' ? $ext : '.' . $ext;
        $unique = $base;
        $suffix = 0;

        // Get unique file name for the file, by appending random suffix.
        while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext))
        {
            $suffix += rand(1, 999);
            $unique = $base.'-'.$suffix;
        }

        $result =  $uploadDirectory . DIRECTORY_SEPARATOR . $unique . $ext;
        // Create an empty target file
        if (!touch($result))
        {
            // Failed
            $result = FALSE;
        }

        if (function_exists('sem_acquire'))
        {
            sem_release($lock);
        }

        return $result;
    }

    /**
     * Deletes all file parts in the chunks folder for files uploaded
     * more than chunksExpireIn seconds ago
     */
    protected function cleanupChunks()
    {
        foreach (scandir($this->chunksFolder) as $item)
        {
            if ($item == '.' || $item == '..')
                continue;

            $path = $this->chunksFolder.DIRECTORY_SEPARATOR . $item;

            if (!is_dir($path))
                continue;

            if (time() - filemtime($path) > $this->chunksExpireIn)
            {
                $this->removeDir($path);
            }
        }
    }

    /**
     * Removes a directory and all files contained inside
     * @param string $dir
     */
    protected function removeDir($dir)
    {
        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..')
                continue;

            if (is_dir($item))
            {
                $this->removeDir($item);
            }
            else
            {
                unlink(join(DIRECTORY_SEPARATOR, array($dir, $item)));
            }
        }

        rmdir($dir);

    }

    /**
     * Converts a given size with units to bytes.
     * @param string $str
     *
     * @return int|string
     */
    protected function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);

        switch($last)
        {
            case 'g': $val *= 1024; break;
            case 'm': $val *= 1024; break;
            case 'k': $val *= 1024; break;
        }

        return $val;
    }

    /**
     * Determines whether a directory can be accessed.
     *
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
        $folderInaccessible = ($isWin) ? !is_writable($directory) : ( !is_writable($directory) && !is_executable($directory) );

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

    protected function setupTmpFolder()
    {
        $this->tmpFolder = PIMCORE_TEMPORARY_DIRECTORY . '/' . 'formbuilder-cache';
        $this->chunksFolder = $this->tmpFolder . '/' . 'chunks';
        $this->filesFolder = $this->tmpFolder . '/' . 'files';
        $this->zipFolder = $this->tmpFolder . '/' . 'zip';

        if( !is_dir( $this->tmpFolder ) )
        {
            mkdir( $this->tmpFolder );
        }

        if( !is_dir( $this->chunksFolder ) )
        {
            //make subfolder for files
            mkdir( $this->chunksFolder );
        }

        if( !is_dir( $this->filesFolder ) )
        {
            //make subfolder for chunks
            mkdir( $this->filesFolder );
        }

        if( !is_dir( $this->zipFolder ) )
        {
            //make subfolder for zip
            mkdir( $this->zipFolder );
        }

        return $this->tmpFolder;
    }

}