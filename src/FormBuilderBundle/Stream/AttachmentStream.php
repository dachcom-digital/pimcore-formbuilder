<?php

namespace FormBuilderBundle\Stream;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\File;
use Pimcore\Logger;
use Pimcore\Model\Asset;

class AttachmentStream implements AttachmentStreamInterface
{
    protected FileLocator $fileLocator;

    public function __construct(FileLocator $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    public function createAttachmentLinks(array $data, string $formName): array
    {
        $files = $this->extractFiles($data);

        if (empty($files)) {
            return [];
        }

        return $files;
    }

    public function createAttachmentAsset($data, $fieldName, $formName): ?Asset
    {
        if (!is_array($data)) {
            return null;
        }

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

    public function removeAttachmentByFileInfo(array $fileInfo): void
    {
        $targetFolder = $this->fileLocator->getFilesFolder();
        $target = implode(DIRECTORY_SEPARATOR, [$targetFolder, $fileInfo['id']]);

        if (!is_dir($target)) {
            return;
        }

        $this->fileLocator->removeDir($target);
    }

    protected function extractFiles(array $data): array
    {
        $files = [];
        foreach ($data as $fileData) {
            $fileDir = $this->fileLocator->getFilesFolder() . '/' . $fileData['id'];
            if (is_dir($fileDir)) {
                $dirFiles = glob($fileDir . '/*');
                if (count($dirFiles) === 1) {
                    $files[] = [
                        'name' => $fileData['fileName'],
                        'id'   => $fileData['id'],
                        'path' => $dirFiles[0]
                    ];
                }
            }
        }

        return $files;
    }
}
