<?php

use DachcomBundle\Test\Util\Autoloader;

define('PIMCORE_PROJECT_ROOT', realpath(getcwd()));

require_once PIMCORE_PROJECT_ROOT . '/vendor/autoload.php';

/**
 * @var $loader \Composer\Autoload\ClassLoader
 */
Autoloader::addNamespace('Pimcore\Tests', PIMCORE_PROJECT_ROOT . '/vendor/pimcore/pimcore/tests/_support');

if (!defined('TESTS_PATH')) {
    define('TESTS_PATH', __DIR__);
}

define('PIMCORE_TEST', true);
