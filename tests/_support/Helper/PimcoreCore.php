<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\Connector\Symfony as SymfonyConnector;
use Codeception\Lib\ModuleContainer;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Pimcore\Cache;
use Pimcore\Config;
use Pimcore\Event\TestEvents;
use Pimcore\Tests\Helper\Pimcore as PimcoreCoreModule;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class PimcoreCore extends PimcoreCoreModule
{
    const DEFAULT_CONFIG_FILE = 'config_default.yml';

    /**
     * @var bool
     */
    protected $kernelHasCustomConfig = false;

    /**
     * @var bool
     */
    protected $kernelHasCustomSuiteConfig = false;

    /**
     * @inheritDoc
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        $this->config = array_merge($this->config, [
            // set specific configuration file for suite
            'configuration_file' => null
        ]);

        parent::__construct($moduleContainer, $config);
    }

    /**
     * @inheritdoc
     */
    public function _initialize()
    {
        $this->setPimcoreEnvironment($this->config['environment']);
        $this->initializeKernel();
        $this->setupDbConnection();
        $this->setPimcoreCacheAvailability('disabled');
    }

    /**
     * @inheritdoc
     */
    public function _beforeSuite($settings = [])
    {
        parent::_beforeSuite($settings);

        if ($this->config['configuration_file'] === null) {
            return;
        }

        if ($this->config['configuration_file'] === self::DEFAULT_CONFIG_FILE) {
            return;
        }

        $configuration = $this->config['configuration_file'];

        $this->kernelHasCustomSuiteConfig = true;
        $this->bootKernelWithConfiguration($configuration);

    }

    /**
     * @inheritdoc
     */
    public function _afterSuite()
    {
        parent::_afterSuite();

        if ($this->kernelHasCustomSuiteConfig !== true) {
            return;
        }

        // config has changed!
        // we need to restore default config before starting a new test!
        $this->bootKernelWithConfiguration(null);
        $this->kernelHasCustomSuiteConfig = false;
    }

    /**
     * @inheritDoc
     */
    public function _after(TestInterface $test)
    {
        parent::_after($test);

        if ($this->kernelHasCustomConfig !== true) {
            return;
        }

        // config has changed!
        // we need to restore default config before starting a new test!
        $this->bootKernelWithConfiguration(null);
        $this->kernelHasCustomConfig = false;

    }

    /**
     * Actor Function to boot symfony with a specific bundle configuration
     *
     * @param string $configuration
     */
    public function haveABootedSymfonyConfiguration(string $configuration)
    {
        $this->kernelHasCustomConfig = true;
        $this->bootKernelWithConfiguration($configuration);
    }

    /**
     * @inheritdoc
     */
    protected function initializeKernel()
    {
        $maxNestingLevel = 200; // Symfony may have very long nesting level
        $xdebugMaxLevelKey = 'xdebug.max_nesting_level';
        if (ini_get($xdebugMaxLevelKey) < $maxNestingLevel) {
            ini_set($xdebugMaxLevelKey, $maxNestingLevel);
        }

        $configFile = null;
        if ($this->config['configuration_file'] !== null) {
            $configFile = $this->config['configuration_file'];
        }

        $fileSystem = new Filesystem();
        $runtimeConfigDir = codecept_data_dir() . 'config' . DIRECTORY_SEPARATOR;
        $runtimeConfigDirConfig = $runtimeConfigDir . DIRECTORY_SEPARATOR . 'config.yml';

        if (!$fileSystem->exists($runtimeConfigDir)) {
            $fileSystem->mkdir($runtimeConfigDir);
        }

        if (!$fileSystem->exists($runtimeConfigDirConfig)) {
            $fileSystem->touch($runtimeConfigDirConfig);
        }

        $this->bootKernelWithConfiguration($configFile);
        $this->setupPimcoreDirectories();
    }

    /**
     * @param string|null $configFile
     */
    protected function bootKernelWithConfiguration($configFile = null)
    {
        $this->setConfiguration($configFile);

        $this->kernel = require __DIR__ . '/../_boot/kernelBuilder.php';
        $this->getKernel()->boot();

        $this->client = new SymfonyConnector($this->kernel, $this->persistentServices, $this->config['rebootable_client']);

        if ($this->config['cache_router'] === true) {
            $this->persistService('router', true);
        }

        // dispatch kernel booted event - will be used from services which need to reset state between tests
        $this->kernel->getContainer()->get('event_dispatcher')->dispatch(TestEvents::KERNEL_BOOTED);
    }

    /**
     * @param null|string $configuration
     */
    protected function setConfiguration($configuration = null)
    {
        $bundleName = getenv('DACHCOM_BUNDLE_NAME');
        $bundleClass = getenv('DACHCOM_BUNDLE_HOME');

        if ($configuration === null) {
            $configuration = self::DEFAULT_CONFIG_FILE;
        }

        Debug::debug(sprintf('[%s] add custom config file %s', strtoupper($bundleName), $configuration));

        $fileSystem = new Filesystem();
        $runtimeConfigDir = codecept_data_dir() . 'config';
        $runtimeConfigDirConfig = $runtimeConfigDir . '/config.yml';

        $resource = $bundleClass . '/_etc/config/bundle/symfony/' . $configuration;

        $fileSystem->dumpFile($runtimeConfigDirConfig, file_get_contents($resource));

        if ($this->kernel === null) {
            return;
        }

        if (Kernel::MAJOR_VERSION === 3) {
            $this->clearCache();
        }
    }

    /**
     * @param $env
     */
    protected function setPimcoreEnvironment($env)
    {
        Config::setEnvironment($env);
    }

    /**
     * @param string $state
     */
    protected function setPimcoreCacheAvailability($state = 'disabled')
    {
        if ($state === 'disabled') {
            Cache::disable();
        } else {
            Cache::enable();
        }
    }

    protected function clearCache()
    {
        Debug::debug('[PIMCORE] Clear Cache!');

        $fileSystem = new Filesystem();
        $cacheDir = PIMCORE_SYMFONY_CACHE_DIRECTORY;

        if (!$fileSystem->exists($cacheDir)) {
            return;
        }

        // see Symfony's cache:clear command
        $oldCacheDir = substr($cacheDir, 0, -1) . ('~' === substr($cacheDir, -1) ? '+' : '~');

        if ($fileSystem->exists($oldCacheDir)) {
            $fileSystem->remove($oldCacheDir);
        }

        $fileSystem->rename($cacheDir, $oldCacheDir);
        $fileSystem->mkdir($cacheDir);
        $fileSystem->remove($oldCacheDir);
    }

}

