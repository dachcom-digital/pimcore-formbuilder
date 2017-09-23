<?php

namespace FormBuilderBundle\EventListener\Core;

use FormBuilderBundle\Tool\FileLocator;
use Pimcore\Event\System\MaintenanceEvent;
use Pimcore\Event\SystemEvents;
use Pimcore\Logger;

class CleanUpListener
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
            SystemEvents::MAINTENANCE => 'onMaintenance',
        ];
    }

    /**
     * @param MaintenanceEvent $ev
     *
     * @return void
     */
    public function onMaintenance(MaintenanceEvent $ev)
    {
        foreach ($this->fileLocator->getFolderContent($this->fileLocator->getFilesFolder()) as $file) {
            Logger::log('Remove formbuilder file: ' . $file);
            $this->fileLocator->removeDir($file->getPathname());
        }

        foreach ($this->fileLocator->getFolderContent($this->fileLocator->getChunksFolder()) as $file) {
            Logger::log('Remove formbuilder file: ' . $file);
            $this->fileLocator->removeDir($file->getPathname());
        }

        foreach ($this->fileLocator->getFolderContent($this->fileLocator->getZipFolder()) as $file) {
            Logger::log('Remove formbuilder file: ' . $file);
            $this->fileLocator->removeDir($file->getPathname());
        }
    }
}