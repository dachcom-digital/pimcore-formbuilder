<?php

use Pimcore\Config;
use Symfony\Component\Debug\Debug;

\Pimcore\Bootstrap::setProjectRoot();
\Pimcore\Bootstrap::boostrap();

$environment = Config::getEnvironment();
$debug = Config::getEnvironmentConfig()->activatesKernelDebugMode($environment);

if ($debug) {
    Debug::enable();
    @ini_set('display_errors', 'On');
}

$kernel = new \DachcomBundle\Test\App\TestAppKernel($environment, $debug);

return $kernel;
