<?php

namespace FormBuilderBundle\Tool;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileLocator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    private $tmpFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . 'formbuilder-cache';

    /**
     * @var string
     */
    private $chunksFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . 'formbuilder-cache/chunks';

    /**
     * @var string
     */
    private $filesFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . 'formbuilder-cache/files';

    /**
     * @var string
     */
    private $zipFolder = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . 'formbuilder-cache/zip';

    /**
     * FileLocator constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        if (!$this->filesystem->exists($this->tmpFolder)) {
            $this->filesystem->mkdir($this->tmpFolder);
        }
    }

    /**
     * @return string
     */
    public function getTmpFolder()
    {
        return $this->tmpFolder;
    }

    /**
     * @return string
     */
    public function getChunksFolder()
    {
        if (!$this->filesystem->exists($this->chunksFolder)) {
            $this->filesystem->mkdir($this->chunksFolder);
        }

        return $this->chunksFolder;
    }

    /**
     * @return string
     */
    public function getFilesFolder()
    {
        if (!$this->filesystem->exists($this->filesFolder)) {
            $this->filesystem->mkdir($this->filesFolder);
        }

        return $this->filesFolder;
    }

    /**
     * return content of $path as Finder-Object.
     *
     * @param string $path
     *
     * @return null|Finder
     */
    public function getFilesFromFolder(string $path)
    {
        if (!$this->filesystem->exists($path)) {
            return null;
        }

        $finder = new Finder();

        return $finder->files()->in($path);
    }

    /**
     * @return string
     */
    public function getZipFolder()
    {
        if (!$this->filesystem->exists($this->zipFolder)) {
            $this->filesystem->mkdir($this->zipFolder);
        }

        return $this->zipFolder;
    }

    /**
     * @param string $dir
     */
    public function removeDir($dir)
    {
        if ($this->filesystem->exists($dir)) {
            $this->filesystem->remove($dir);
        }
    }

    /**
     * @param string $folder
     * @param string $minStorageAge
     *
     * @return Finder
     */
    public function getFolderContent($folder = '', $minStorageAge = '< 24 hour ago')
    {
        $finder = new Finder();
        $minStorageAge = empty($minStorageAge) ? '< 0 minute ago' : $minStorageAge;

        return $finder->in($folder)->date($minStorageAge)->depth('== 0')->directories();
    }
}
