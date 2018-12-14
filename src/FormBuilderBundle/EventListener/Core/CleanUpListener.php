<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\Event\System\MaintenanceEvent;
use Pimcore\Event\SystemEvents;
use Pimcore\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CleanUpListener implements EventSubscriberInterface
{
    /**
     * @var FileLocator
     */
    protected $fileLocator;

    /**
     * Worker constructor.
     *
     * @param FileLocator $fileLocator
     */
    public function __construct(FileLocator $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SystemEvents::MAINTENANCE => ['onMaintenance'],
        ];
    }

    /**
     * @return void
     */
    public function onMaintenance()
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