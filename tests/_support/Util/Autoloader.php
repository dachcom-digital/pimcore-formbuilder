<?php

namespace DachcomBundle\Test\Util;

use Codeception\Util\Autoload;

class Autoloader extends Autoload
{
    /**
     * @var bool
     */
    protected static $reg = false;

    /**
     * @param string $prefix
     * @param string $base_dir
     * @param bool   $prepend
     */
    public static function addNamespace($prefix, $base_dir, $prepend = false)
    {
        if (!self::$reg) {
            spl_autoload_register([__CLASS__, 'load'], true, true);
            self::$reg = true;
        }

        parent::addNamespace($prefix, $base_dir, $prepend);
    }
}
