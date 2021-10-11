<?php

namespace FormBuilderBundle\Tool;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileLocator
{
    protected Filesystem $filesystem;
    private string $tmpFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/formbuilder-cache';
    private string $chunksFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/formbuilder-cache/chunks';
    private string $filesFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/formbuilder-cache/files';
    private string $zipFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/formbuilder-cache/zip';

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        if (!$this->filesystem->exists($this->tmpFolder)) {
            $this->filesystem->mkdir($this->tmpFolder);
        }
    }

    public function getTmpFolder(): string
    {
        return $this->tmpFolder;
    }

    public function getChunksFolder(): string
    {
        if (!$this->filesystem->exists($this->chunksFolder)) {
            $this->filesystem->mkdir($this->chunksFolder);
        }

        return $this->chunksFolder;
    }

    public function getFilesFolder(): string
    {
        if (!$this->filesystem->exists($this->filesFolder)) {
            $this->filesystem->mkdir($this->filesFolder);
        }

        return $this->filesFolder;
    }

    public function getFilesFromFolder(string $path): ?Finder
    {
        if (!$this->filesystem->exists($path)) {
            return null;
        }

        $finder = new Finder();

        return $finder->files()->in($path);
    }

    public function getZipFolder(): string
    {
        if (!$this->filesystem->exists($this->zipFolder)) {
            $this->filesystem->mkdir($this->zipFolder);
        }

        return $this->zipFolder;
    }

    public function assertDir(string $path): void
    {
        if ($this->filesystem->exists($path) === true) {
            return;
        }

        $this->filesystem->mkdir($path, 0755, true);
    }

    public function removeDir(string $dir): void
    {
        if ($this->filesystem->exists($dir)) {
            $this->filesystem->remove($dir);
        }
    }

    public function getFolderContent(string $folder = '', string $minStorageAge = '< 24 hour ago'): Finder
    {
        $finder = new Finder();
        $minStorageAge = empty($minStorageAge) ? '< 0 minute ago' : $minStorageAge;

        return $finder->in($folder)->date($minStorageAge)->depth('== 0')->directories();
    }
}
