<?php

namespace DachcomBundle\Test\Util;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileGeneratorHelper
{
    /**
     * @param     $fileName
     * @param int $fileSizeInMb
     */
    public static function generateDummyFile($fileName, $fileSizeInMb = 1)
    {
        $dataDir = self::getStoragePath();

        if (file_exists($dataDir . $fileName)) {
            return;
        }

        $bytes = $fileSizeInMb * 1000000;
        $fp = fopen($dataDir . $fileName, 'w');
        fseek($fp, $bytes - 1, SEEK_CUR);
        fwrite($fp, 'a');
        fclose($fp);
    }

    public static function preparePaths()
    {
        $fs = new Filesystem();
        $dataDir = codecept_data_dir();

        if (!$fs->exists($dataDir . 'generated')) {
            $fs->mkdir($dataDir . 'generated');
        }

        if (!$fs->exists($dataDir . 'downloads')) {
            $fs->mkdir($dataDir . 'downloads');
        }
    }

    /**
     * @return string
     */
    public static function getStoragePath()
    {
        $dataDir = codecept_data_dir() . 'generated' . DIRECTORY_SEPARATOR;
        return $dataDir;
    }

    /**
     * @return string
     */
    public static function getDownloadPath()
    {
        $dataDir = codecept_data_dir() . 'downloads' . DIRECTORY_SEPARATOR;
        return $dataDir;
    }

    public static function cleanUp()
    {
        $finder = new Finder();
        $fs = new Filesystem();

        $dataDir = self::getStoragePath();
        if ($fs->exists($dataDir)) {
            $fs->remove($finder->ignoreDotFiles(true)->in($dataDir));
        }

        $downloadDir = self::getDownloadPath();
        if ($fs->exists($downloadDir)) {
            $fs->remove($finder->ignoreDotFiles(true)->in($downloadDir));
        }
    }
}
