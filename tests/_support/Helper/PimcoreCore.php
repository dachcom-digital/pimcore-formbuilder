<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Connector\Symfony as SymfonyConnector;
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
     * @inheritDoc
     */
    public function _after(\Codeception\TestInterface $test)
    {
        parent::_after($test);

        // config has changed, we need to restore default config before starting a new test!
        if ($this->kernelHasCustomConfig === true) {
            $this->clearCache();
            $this->bootKernelWithConfiguration(null);
            $this->kernelHasCustomConfig = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function _afterSuite($settings = [])
    {
        $this->clearCache();
        parent::_beforeSuite($settings);
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

        $this->bootKernelWithConfiguration($configFile);
        $this->setupPimcoreDirectories();
    }

    /**
     * @param $configuration
     */
    protected function bootKernelWithConfiguration($configuration)
    {

        if ($configuration === null) {
            $configuration = 'config_default.yml';
        }

        putenv('DACHCOM_BUNDLE_CONFIG_FILE=' . $configuration);

        $this->kernel = require __DIR__ . '/../../kernelBuilder.php';
        $this->getKernel()->boot();

        $this->client = new SymfonyConnector($this->kernel, $this->persistentServices, $this->config['rebootable_client']);

        if ($this->config['cache_router'] === true) {
            $this->persistService('router', true);
        }

        // dispatch kernel booted event - will be used from services which need to reset state between tests
        $this->kernel->getContainer()->get('event_dispatcher')->dispatch(TestEvents::KERNEL_BOOTED);
    }

    /**
     * @param bool $force
     */
    protected function clearCache($force = true)
    {
        $fileSystem = new Filesystem();

        try {
            $fileSystem->remove(PIMCORE_PROJECT_ROOT . '/var/cache');
            $fileSystem->mkdir(PIMCORE_PROJECT_ROOT . '/var/cache');
        } catch (\Exception $e) {
            //try again later if "directory not empty" error occurs.
            if ($force === true) {
                sleep(1);
                $this->clearCache(false);
            }
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

    /**
     * Actor Function to boot symfony with a specific bundle configuration
     *
     * @param string $configuration
     */
    public function haveABootedSymfonyConfiguration(string $configuration)
    {
        $this->kernelHasCustomConfig = true;
        $this->clearCache();
        $this->bootKernelWithConfiguration($configuration);
    }
}
