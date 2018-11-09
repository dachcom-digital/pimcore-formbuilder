<?php

namespace DachcomBundle\Test\Util;

class VersionHelper
{
    /**
     * @param string $version
     *
     * @return mixed
     */
    public static function pimcoreVersionIsEqualThan(string $version)
    {
        return version_compare(self::getPimcoreVersion(), $version, '=');
    }

    /**
     * @param string $version
     *
     * @return mixed
     */
    public static function pimcoreVersionIsGreaterThan(string $version)
    {
        return version_compare(self::getPimcoreVersion(), $version, '>');
    }

    /**
     * @param string $version
     *
     * @return mixed
     */
    public static function pimcoreVersionIsGreaterOrEqualThan(string $version)
    {
        return version_compare(self::getPimcoreVersion(), $version, '>=');
    }

    /**
     * @param string $version
     *
     * @return mixed
     */
    public static function pimcoreVersionIsLowerThan(string $version)
    {
        return version_compare(self::getPimcoreVersion(), $version, '<');
    }

    /**
     * @param string $version
     *
     * @return mixed
     */
    public static function pimcoreVersionIsLowerOrEqualThan(string $version)
    {
        return version_compare(self::getPimcoreVersion(), $version, '<=');
    }

    /**
     * @return string
     */
    private static function getPimcoreVersion()
    {
        return preg_replace('/[^0-9.]/', '', \Pimcore\Version::getVersion());
    }
}
