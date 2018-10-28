<?php

namespace DachcomBundle\Test\Util;

use Symfony\Component\Filesystem\Filesystem;

class FileGeneratorHelper
{
    /**
     * @param     $fileName
     * @param int $fileSizeInMb
     */
    public static function generateDummyFile($fileName, $fileSizeInMb = 1)
    {
        self::prepareStoragePath();

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

    public static function prepareStoragePath()
    {
        $fs = new Filesystem();
        $dataDir = codecept_data_dir();
        if (!$fs->exists($dataDir . 'generated')) {
            $fs->mkdir($dataDir . 'generated');
        }
    }

    /**
     * @return string
     */
    public static function getStoragePath()
    {
        $dataDir = codecept_data_dir() . 'generated/';

        return $dataDir;
    }

    public static function cleanUp()
    {
        $fs = new Filesystem();
        $dataDir = self::getStoragePath();

        if ($fs->exists($dataDir)) {
            $fs->remove($dataDir);
        }
    }
}
