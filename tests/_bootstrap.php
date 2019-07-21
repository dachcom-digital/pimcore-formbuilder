<?php

use Pimcore\Bootstrap;
use DachcomBundle\Test\Util\Autoloader;

include __DIR__ . '/../../../vendor/autoload.php';

define('PIMCORE_KERNEL_CLASS', '\DachcomBundle\Test\App\TestAppKernel');

Bootstrap::setProjectRoot();
Bootstrap::bootstrap();

/**
 * @var $loader \Composer\Autoload\ClassLoader
 */
Autoloader::addNamespace('Pimcore\Tests', PIMCORE_PROJECT_ROOT . '/vendor/pimcore/pimcore/tests/_support');
Autoloader::addNamespace('DachcomBundle\Test\App\Services', __DIR__ . '/_support/App/Services');

if (!defined('TESTS_PATH')) {
    define('TESTS_PATH', __DIR__);
}

define('PIMCORE_TEST', true);
