<?php

namespace FormBuilderBundle\Stream;

use Doctrine\DBAL\Query\QueryBuilder;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalsEvent;
use FormBuilderBundle\Tool\FileLocator;
use Pimcore\Logger;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttachmentStream implements AttachmentStreamInterface
{
    protected const PACKAGE_IDENTIFIER = 'formbuilder_package_identifier';
    protected const SIGNAL_CLEAN_UP = 'tmp_file_attachment_stream';

    protected EventDispatcherInterface $eventDispatcher;
    protected FileLocator $fileLocator;

    public function __construct(EventDispatcherInterface $eventDispatcher, FileLocator $fileLocator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->fileLocator = $fileLocator;
    }

    /**
     * @return array<int, File>
     */
    public function createAttachmentLinks(array $data, string $formName): array
    {
        return $this->extractFiles($data);
    }

    public function createAttachmentAsset($data, $fieldName, $formName): ?Asset
    {
        if (!is_array($data)) {
            return null;
        }

        $files = $this->extractFiles($data);

        if (count($files) === 0) {
            return null;
        }

        $packageIdentifier = '';
        foreach ($files as $file) {
            $packageIdentifier .= sprintf('%s-%s-%s-%s', filesize($file->getPath()), $file->getId(), $file->getPath(), $file->getName());
        }

        // create package identifier to check if we just in another channel
        $packageIdentifier = md5($packageIdentifier);

        $formName = \Pimcore\File::getValidFilename($formName);
        $zipKey = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 5);
        $zipFileName = sprintf('%s-%s.zip', \Pimcore\File::getValidFilename($fieldName), $zipKey);
        $zipPath = sprintf('%s/%s', $this->fileLocator->getZipFolder(), $zipFileName);

        $existingAssetPackage = $this->findExistingAssetPackage($packageIdentifier, $formName);

        if ($existingAssetPackage instanceof Asset) {
            return $existingAssetPackage;
        }

        try {
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $file) {
                $zip->addFile($file->getPath(), $file->getName());
            }

            $zip->close();

        } catch (\Exception $e) {
            Logger::error(sprintf('Error while creating attachment zip (%s): %s', $zipPath, $e->getMessage()));

            return null;
        }

        if (!file_exists($zipPath)) {
            Logger::error(sprintf('zip path does not exist (%s)', $zipPath));

            return null;
        }

        $formDataParentFolder = Asset\Folder::getByPath('/formdata');

        if (!$formDataParentFolder instanceof Asset\Folder) {
            Logger::error('parent folder does not exist (/formdata)!');

            return null;
        }

        $formFolderExists = Asset\Service::pathExists(sprintf('/formdata/%s', $formName));

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
            $formDataFolder = Asset\Folder::getByPath(sprintf('/formdata/%s', $formName));
        }

        if (!$formDataFolder instanceof Asset\Folder) {
            Logger::error(sprintf('Error while creating form data folder (/formdata/%s)', $formName));

            return null;
        }

        $assetData = [
            'data'     => file_get_contents($zipPath),
            'filename' => $zipFileName
        ];

        try {
            $asset = Asset::create($formDataFolder->getId(), $assetData, false);
            $asset->setProperty(self::PACKAGE_IDENTIFIER, 'text', $packageIdentifier, false, false);
            $asset->save();
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        } catch (\Exception $e) {
            Logger::error(sprintf('Error while storing asset in pimcore (%s): %s', $zipPath, $e->getMessage()));

            return null;
        }

        return $asset;
    }

    /**
     * @internal
     */
    public function cleanUp(OutputWorkflowSignalsEvent $signalsEvent): void
    {
        // keep assets if guard exception occurs: use may want to retry!

        if ($signalsEvent->hasGuardException() === true) {
            return;
        }

        foreach ($signalsEvent->getSignalsByName(self::SIGNAL_CLEAN_UP) as $signal) {
            /** @var File $attachmentFile */
            foreach ($signal->getData() as $attachmentFile) {
                $this->removeAttachmentFile($attachmentFile);
            }
        }
    }

    protected function removeAttachmentFile(File $attachmentFile): void
    {
        $targetFolder = $this->fileLocator->getFilesFolder();
        $target = implode(DIRECTORY_SEPARATOR, [$targetFolder, $attachmentFile->getId()]);

        if (!is_dir($target)) {
            return;
        }

        $this->fileLocator->removeDir($target);
    }

    /**
     * @return array<int, File>
     */
    protected function extractFiles(array $data): array
    {
        $files = [];
        foreach ($data as $fileData) {

            $fileId = (string) $fileData['id'];
            $fileDir = sprintf('%s/%s', $this->fileLocator->getFilesFolder(), $fileId);

            if (is_dir($fileDir)) {
                $dirFiles = glob($fileDir . '/*');
                if (count($dirFiles) === 1) {
                    $files[] = new File($fileId, $fileData['fileName'], $dirFiles[0]);
                }
            }
        }

        // add signal for later clean up
        $this->eventDispatcher->dispatch(
            new OutputWorkflowSignalEvent(self::SIGNAL_CLEAN_UP, $files),
            OutputWorkflowSignalEvent::NAME
        );

        return $files;
    }

    protected function findExistingAssetPackage(string $packageIdentifier, string $formName): ?Asset
    {
        $assetListing = new Asset\Listing();
        $assetListing->addConditionParam('`assets`.path = ?', sprintf('/formdata/%s/', $formName));
        $assetListing->addConditionParam('`properties`.data = ?', $packageIdentifier);
        $assetListing->setLimit(1);

        $assetListing->onCreateQueryBuilder(function (QueryBuilder $queryBuilder) {
            $queryBuilder->leftJoin(
                'assets',
                'properties',
                'properties',
                sprintf('properties.`cid` = assets.`id` AND properties.`name` = "%s"', self::PACKAGE_IDENTIFIER)
            );
        });

        $assets = $assetListing->getAssets();

        if (count($assets) === 0) {
            return null;
        }

        return $assets[0];
    }
}
