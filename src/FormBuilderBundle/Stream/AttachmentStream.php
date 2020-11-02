<?php

namespace FormBuilderBundle\Stream;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\File;
use Pimcore\Logger;
use Pimcore\Model\Asset;

class AttachmentStream implements AttachmentStreamInterface
{
    /**
     * @var FileLocator
     */
    protected $fileLocator;

    /**
     * @param FileLocator $fileLocator
     */
    public function __construct(FileLocator $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttachmentLinks($data, $formName)
    {
        $files = $this->extractFiles($data);

        if (empty($files)) {
            return [];
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttachmentAsset($data, $formName)
    {
        if (!is_array($data)) {
            return null;
        }

        $fieldName = $this->extractFieldName($data);
        $files = $this->extractFiles($data);

        if (empty($files)) {
            return null;
        }

        $key = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 5);
        $zipFileName = File::getValidFilename($fieldName) . '-' . $key . '.zip';
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
                $this->removeAttachmentByFileInfo($fileInfo);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            Logger::log('Error while creating zip for FormBuilder (' . $zipPath . '): ' . $e->getMessage());

            return null;
        }

        if (!file_exists($zipPath)) {
            Logger::log('zip path does not exist (' . $zipPath . ')');

            return null;
        }

        $formDataFolder = null;
        $formDataParentFolder = Asset\Folder::getByPath('/formdata');

        if (!$formDataParentFolder instanceof Asset\Folder) {
            Logger::error('parent folder does not exist (/formdata)!');

            return null;
        }

        $formName = File::getValidFilename($formName);
        $formFolderExists = Asset\Service::pathExists('/formdata/' . $formName);

        if ($formFolderExists === false) {
            $formDataFolder = new Asset\Folder();
            $formDataFolder->setCreationDate(time());
            $formDataFolder->setLocked(true);
            $formDataFolder->setUserOwner(1);
            $formDataFolder->setUserModification(0);
            $formDataFolder->setParentId($formDataParentFolder->getId());
            $formDataFolder->setFilename($formName);

            try {
                $formDataFolder->save();
            } catch (\Exception $e) {
                // fail silently.
            }
        } else {
            $formDataFolder = Asset\Folder::getByPath('/formdata/' . $formName);
        }

        if (!$formDataFolder instanceof Asset\Folder) {
            Logger::error('Error while creating formDataFolder: (/formdata/' . $formName . ')');

            return null;
        }

        $assetData = [
            'data'     => file_get_contents($zipPath),
            'filename' => $zipFileName
        ];

        try {
            $asset = Asset::create($formDataFolder->getId(), $assetData, false);
            $asset->save();
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        } catch (\Exception $e) {
            Logger::log('Error while storing asset in Pimcore (' . $zipPath . '): ' . $e->getMessage());

            return null;
        }

        return $asset;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAttachmentByFileInfo(array $fileInfo)
    {
        $targetFolder = $this->fileLocator->getFilesFolder();
        $target = join(DIRECTORY_SEPARATOR, [$targetFolder, $fileInfo['uuid']]);

        if (!is_dir($target)) {
            return;
        }

        $this->fileLocator->removeDir($target);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function extractFiles(array $data)
    {
        $files = [];
        foreach ($data as $fileData) {
            $fileDir = $this->fileLocator->getFilesFolder() . '/' . $fileData['uuid'];
            if (is_dir($fileDir)) {
                $dirFiles = glob($fileDir . '/*');
                if (count($dirFiles) === 1) {
                    $files[] = [
                        'name' => $fileData['fileName'],
                        'uuid' => $fileData['uuid'],
                        'path' => $dirFiles[0]
                    ];
                }
            }
        }

        return $files;
    }

    /**
     * @param array $data
     *
     * @return string|null
     */
    protected function extractFieldName(array $data)
    {
        $fieldName = null;

        if (count($data) > 0) {
            return $data[0]['fieldName'];
        }

        return $fieldName;
    }
}
