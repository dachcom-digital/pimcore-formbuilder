<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Connector\Symfony as SymfonyConnector;
use Codeception\Util\Debug;
use Pimcore\Cache;
use Pimcore\Config;
use Pimcore\Event\TestEvents;
use Pimcore\Tests\Helper\Pimcore as PimcoreCoreModule;
use Symfony\Component\Filesystem\Filesystem;

class PimcoreCore extends PimcoreCoreModule
{
    /**
     * @var bool
     */
    protected $kernelHasCustomConfig = false;

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
     * @inheritDoc
     */
    public function _after(\Codeception\TestInterface $test)
    {
        parent::_after($test);

        // config has changed, we need to restore default config before starting a new test!
        if ($this->kernelHasCustomConfig === true) {
            $this->bootKernelWithConfiguration(null);
            $this->kernelHasCustomConfig = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function _afterSuite()
    {
        parent::_afterSuite();
        $this->bootKernelWithConfiguration(null);
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
    protected function bootKernelWithConfiguration($configFile)
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
            $configuration = 'config_default.yml';
        }

        Debug::debug(sprintf('[%s] add custom config file %s', strtoupper($bundleName), $configuration));


        $fileSystem = new Filesystem();
        $runtimeConfigDir = codecept_data_dir() . 'config' . DIRECTORY_SEPARATOR;
        $runtimeConfigDirConfig = $runtimeConfigDir . DIRECTORY_SEPARATOR . 'config.yml';

        $resource = $bundleClass . '/_etc/config/bundle/symfony' . DIRECTORY_SEPARATOR . $configuration;
        $fileSystem->dumpFile($runtimeConfigDirConfig, file_get_contents($resource));
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
}

