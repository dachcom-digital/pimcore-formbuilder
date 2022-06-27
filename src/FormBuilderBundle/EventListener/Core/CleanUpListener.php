<?php

namespace FormBuilderBundle\EventListener\Core;

use League\Flysystem\FilesystemOperator;
use Pimcore\Logger;
use Pimcore\Maintenance\TaskInterface;

class CleanUpListener implements TaskInterface
{
    public function __construct(
        protected FilesystemOperator $formbuilderChunkStorage,
        protected FilesystemOperator $formbuilderFilesStorage,
    )
    {
    }

    public function execute(): void
    {
        foreach ($this->formbuilderFilesStorage->listContents('/') as $file) {
            Logger::log('Remove form builder files folder: ' . $file->path());

            if ($file->isDir()) {
                $this->formbuilderFilesStorage->deleteDirectory($file->path());
            }
            else {
                $this->formbuilderFilesStorage->delete($file->path());
            }
        }

        foreach ($this->formbuilderChunkStorage->listContents('/') as $file) {
            Logger::log('Remove form builder chunks folder: ' . $file->path());

            if ($file->isDir()) {
                $this->formbuilderChunkStorage->deleteDirectory($file->path());
            }
            else {
                $this->formbuilderChunkStorage->delete($file->path());
            }
        }
    }
}
