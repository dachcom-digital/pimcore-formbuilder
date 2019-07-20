<?php

use Pimcore\Config;
use Pimcore\Bootstrap;
use Symfony\Component\Debug\Debug;
use DachcomBundle\Test\App\TestAppKernel;

Bootstrap::setProjectRoot();
Bootstrap::bootstrap();

$environment = Config::getEnvironment();
$debug = Config::getEnvironmentConfig()->activatesKernelDebugMode($environment);

if ($debug) {
    Debug::enable();
    @ini_set('display_errors', 'On');
}

$kernel = new TestAppKernel($environment, $debug);

return $kernel;
