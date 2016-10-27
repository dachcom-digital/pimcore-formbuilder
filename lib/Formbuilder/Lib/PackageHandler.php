<?php

namespace Formbuilder\Lib;

use \Formbuilder\Tool\File;
use \Pimcore\Model\Asset;

class PackageHandler {

    public function __construct()
    {
        File::setupTmpFolder();
    }

    /**
     * @param $data
     * @param string $formName
     * @param int $templateId
     *
     * @return bool|null|Asset
     */
    public function createZipAsset( $data, $formName, $templateId )
    {
        if( !is_array( $data ) )
        {
            return FALSE;
        }

        $files = array();

        //Find all Files!
        foreach( $data as $folderName => $fileName )
        {
            $fileDir = File::getFilesFolder() . '/' . $folderName;
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
        $zipPath = File::getZipFolder() . '/' . $zipFileName;

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
                $targetFolder = File::getFilesFolder();
                $target = join(DIRECTORY_SEPARATOR, array($targetFolder, $fileInfo['uuid']));

                if ( is_dir($target) )
                {
                    File::removeDir($target);
                }

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

}