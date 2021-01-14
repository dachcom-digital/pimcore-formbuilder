<?php

$frameworkPath = getenv('PIMCORE_CODECEPTION_FRAMEWORK');
$bundleTestPath = getenv('TEST_BUNDLE_TEST_DIR');

$bootstrap = sprintf('%s/src/_bootstrap.php', $frameworkPath);

include_once $bootstrap;
