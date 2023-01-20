<?php

namespace FormBuilderBundle\EventListener\Core;

use Carbon\Carbon;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Pimcore\Logger;
use Pimcore\Maintenance\TaskInterface;

class CleanUpListener implements TaskInterface
{
    public function __construct(
        protected FilesystemOperator $formBuilderChunkStorage,
        protected FilesystemOperator $formBuilderFilesStorage,
    ) {
    }

    public function execute(): void
    {
        $minimumModifiedDelta = Carbon::now()->subHour();

        foreach ($this->formBuilderFilesStorage->listContents('/') as $file) {
            $this->remove($minimumModifiedDelta, $file);
        }

        foreach ($this->formBuilderChunkStorage->listContents('/') as $file) {
            $this->remove($minimumModifiedDelta, $file);
        }
    }

    protected function remove(Carbon $minimumModifiedDelta, StorageAttributes $file): void
    {
        if (!$minimumModifiedDelta->greaterThan(Carbon::createFromTimestamp($file->lastModified()))) {
            return;
        }

        if ($file->isDir() && $minimumModifiedDelta->greaterThan(Carbon::createFromTimestamp($file->lastModified()))) {
            $this->formBuilderFilesStorage->deleteDirectory($file->path());
        } elseif ($minimumModifiedDelta->greaterThan(Carbon::createFromTimestamp($file->lastModified()))) {
            $this->formBuilderFilesStorage->delete($file->path());
        }

        Logger::log(sprintf('Removing outdated form builder tmp %s: %s', $file->isDir() ? 'directory' : 'file', $file->path()));
    }
}
