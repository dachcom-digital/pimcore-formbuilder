<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\Logger;
use Pimcore\Maintenance\TaskInterface;

class CleanUpListener implements TaskInterface
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

    public function execute()
    {
        foreach ($this->fileLocator->getFolderContent($this->fileLocator->getFilesFolder()) as $file) {
            Logger::log('Remove form builder files folder: ' . $file);
            $this->fileLocator->removeDir($file->getPathname());
        }

        foreach ($this->fileLocator->getFolderContent($this->fileLocator->getChunksFolder()) as $file) {
            Logger::log('Remove form builder chunk folder: ' . $file);
            $this->fileLocator->removeDir($file->getPathname());
        }

        foreach ($this->fileLocator->getFolderContent($this->fileLocator->getZipFolder()) as $file) {
            Logger::log('Remove form builder zip folder: ' . $file);
            $this->fileLocator->removeDir($file->getPathname());
        }
    }
}
