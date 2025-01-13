<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Stream;

use Doctrine\DBAL\Query\QueryBuilder;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;
use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalsEvent;
use FormBuilderBundle\EventSubscriber\SignalSubscribeHandler;
use League\Flysystem\FilesystemOperator;
use Pimcore\Logger;
use Pimcore\Model\Asset;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttachmentStream implements AttachmentStreamInterface
{
    public const SIGNAL_CLEAN_UP = 'tmp_file_attachment_stream';
    protected const PACKAGE_IDENTIFIER = 'formbuilder_package_identifier';

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected FilesystemOperator $formBuilderFilesStorage
    ) {
    }

    /**
     * @return array<int, File>
     */
    public function createAttachmentLinks(array $data, string $formName): array
    {
        return $this->extractFiles($data)->getFiles();
    }

    public function createAttachmentAsset($data, $fieldName, $formName): ?Asset
    {
        if (!is_array($data)) {
            return null;
        }

        $fileStack = $this->extractFiles($data);

        if ($fileStack->count() === 0) {
            return null;
        }

        $packageIdentifier = '';
        foreach ($fileStack->getFiles() as $file) {
            $packageIdentifier .= sprintf('%s-%s-%s-%s', $this->formBuilderFilesStorage->fileSize($file->getPath()), $file->getId(), $file->getPath(), $file->getName());
        }

        // create package identifier to check if we just in another channel
        $packageIdentifier = md5($packageIdentifier);

        $formName = \Pimcore\File::getValidFilename($formName);
        $zipKey = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 5);
        $zipFileName = sprintf('%s-%s.zip', \Pimcore\File::getValidFilename($fieldName), $zipKey);
        $zipPath = sprintf('%s/%s', PIMCORE_SYSTEM_TEMP_DIRECTORY, $zipFileName);

        $existingAssetPackage = $this->findExistingAssetPackage($packageIdentifier, $formName);

        if ($existingAssetPackage instanceof Asset) {
            return $existingAssetPackage;
        }

        try {
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($fileStack->getFiles() as $file) {
                $zip->addFromString($file->getName(), $this->formBuilderFilesStorage->read($file->getPath()));
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

            unlink($zipPath);
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
        // keep assets:
        // - if broadcasting channel is initiating funnel
        // - if broadcasting channel is processing funnel and not done yet
        // - if guard exception occurs: user may want to retry!

        if ($signalsEvent->getChannel() === SignalSubscribeHandler::CHANNEL_FUNNEL_INITIATE) {
            return;
        }

        if ($signalsEvent->getChannel() === SignalSubscribeHandler::CHANNEL_FUNNEL_PROCESS && $signalsEvent->getContextItem('funnel_shutdown') === false) {
            return;
        }

        if ($signalsEvent->hasGuardException() === true) {
            return;
        }

        foreach ($signalsEvent->getSignalsByName(self::SIGNAL_CLEAN_UP) as $signal) {
            $fileStack = $signal->getData();
            if (!$fileStack instanceof FileStack) {
                continue;
            }

            foreach ($fileStack->getFiles() as $attachmentFile) {
                $this->removeAttachmentFile($attachmentFile);
            }
        }
    }

    protected function removeAttachmentFile(File $attachmentFile): void
    {
        if ($this->formBuilderFilesStorage->directoryExists($attachmentFile->getId())) {
            $this->formBuilderFilesStorage->deleteDirectory($attachmentFile->getId());
        }
    }

    protected function extractFiles(array $data): FileStack
    {
        $files = new FileStack();
        foreach ($data as $fileData) {
            $fileId = (string) $fileData['id'];
            if ($this->formBuilderFilesStorage->directoryExists($fileId)) {
                $dirFiles = $this->formBuilderFilesStorage->listContents($fileId);
                $flyFiles = iterator_to_array($dirFiles->getIterator());
                if (count($flyFiles) === 1) {
                    $files->addFile(new File($fileId, $fileData['fileName'], $flyFiles[0]->path()));
                }
            }
        }

        // add signal for later clean up
        $this->eventDispatcher->dispatch(new OutputWorkflowSignalEvent(self::SIGNAL_CLEAN_UP, $files), OutputWorkflowSignalEvent::NAME);

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
                sprintf('properties.`cid` = assets.`id` AND properties.`ctype` = "asset" AND properties.`name` = "%s"', self::PACKAGE_IDENTIFIER)
            );
        });

        $assets = $assetListing->getAssets();

        if (count($assets) === 0) {
            return null;
        }

        return $assets[0];
    }
}
