<?php

namespace Formbuilder\Tool;

class File {

    /**
     * @var string
     */
    static protected $tmpFolder = '' ;

    /**
     * @var string
     */
    static protected $chunksFolder = '';

    /**
     * @var string
     */
    static protected $filesFolder = '';

    /**
     * @var string
     */
    static protected $zipFolder = '';

    /**
     * @return string
     */
    public static function getTmpFolder()
    {
        return self::$tmpFolder;
    }

    /**
     * @return string
     */
    public static function getChunksFolder()
    {
        return self::$chunksFolder;
    }

    /**
     * @return string
     */
    public static function getFilesFolder()
    {
        return self::$filesFolder;
    }

    /**
     * @return string
     */
    public static function getZipFolder()
    {
        return self::$zipFolder;
    }

    /**
     * Removes a directory and all files contained inside
     * @param string $dir
     *
     * @return bool
     */
    public static function removeDir($dir)
    {
        if( is_file( $dir ) )
        {
            unlink( $dir );
            return TRUE;
        }

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..')
                continue;

            if (is_dir($item))
            {
                return self::removeDir($item);
            }
            else
            {
                unlink(join(DIRECTORY_SEPARATOR, array($dir, $item)));
            }
        }

        return rmdir($dir);

    }

    /**
     * @param string $folder
     * @param int $minStorageAge 86400 = 24h
     *
     * @return array
     */
    public static function getFolderContent( $folder = '', $minStorageAge = 0 )
    {
        $now = time();
        $data = array();

        foreach(glob($folder . '/*') as $file)
        {
            if( $minStorageAge === 0 )
            {
                $data[] = $file;
                continue;
            }

            if( ($now - filemtime($file)) >= $minStorageAge )
            {
                $data[] = $file;
            }

        }

        return $data;

    }

    /**
     * @return string
     */
    public static function setupTmpFolder()
    {
        self::$tmpFolder = PIMCORE_TEMPORARY_DIRECTORY . '/' . 'formbuilder-cache';
        self::$chunksFolder = self::$tmpFolder . '/' . 'chunks';
        self::$filesFolder = self::$tmpFolder . '/' . 'files';
        self::$zipFolder = self::$tmpFolder . '/' . 'zip';

        if( !is_dir( self::$tmpFolder ) )
        {
            mkdir( self::$tmpFolder );
        }

        if( !is_dir( self::$chunksFolder ) )
        {
            //make subfolder for files
            mkdir( self::$chunksFolder );
        }

        if( !is_dir( self::$filesFolder ) )
        {
            //make subfolder for chunks
            mkdir( self::$filesFolder );
        }

        if( !is_dir( self::$zipFolder ) )
        {
            //make subfolder for zip
            mkdir( self::$zipFolder );
        }

        return self::$tmpFolder;
    }
}