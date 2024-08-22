<?php

namespace FormBuilderBundle\Maintenance;

use Carbon\Carbon;
use FormBuilderBundle\Manager\DoubleOptInManager;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Pimcore\Logger;
use Pimcore\Maintenance\TaskInterface;

class CleanUpTask implements TaskInterface
{
    public function __construct(
        protected DoubleOptInManager $doubleOptInManager,
        protected FilesystemOperator $formBuilderChunkStorage,
        protected FilesystemOperator $formBuilderFilesStorage,
    ) {
    }

    public function execute(): void
    {
        $this->cleanUpFileStorage();
        $this->cleanUpDoubleOptInSessions();
    }

    protected function cleanUpFileStorage(): void
    {
        $minimumModifiedDelta = Carbon::now()->subHour();

        foreach ($this->formBuilderFilesStorage->listContents('/') as $file) {
            $this->remove($minimumModifiedDelta, $file);
        }

        foreach ($this->formBuilderChunkStorage->listContents('/') as $file) {
            $this->remove($minimumModifiedDelta, $file);
        }
    }

    protected function cleanUpDoubleOptInSessions(): void
    {
        if (!$this->doubleOptInManager->doubleOptInEnabled()) {
            return;
        }

        /** @var DoubleOptInSessionInterface $session */
        foreach ($this->doubleOptInManager->getOutDatedDoubleOptInSessions() as $session) {
            $this->doubleOptInManager->deleteDoubleOptInSession($session);
        }
    }

    protected function remove(Carbon $minimumModifiedDelta, StorageAttributes $file): void
    {
        if (!$minimumModifiedDelta->greaterThan(Carbon::createFromTimestamp($file->lastModified()))) {
            return;
        }

        if ($file->isDir()) {
            $this->formBuilderFilesStorage->deleteDirectory($file->path());
        } else {
            $this->formBuilderFilesStorage->delete($file->path());
        }

        Logger::log(sprintf('Removing outdated form builder tmp %s: %s', $file->isDir() ? 'directory' : 'file', $file->path()));
    }
}
