<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;

class PimcoreBundleCore extends Module
{
    /**
     * @inheritDoc
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        $this->config = array_merge($this->config, [
            'run_installer' => false
        ]);

        parent::__construct($moduleContainer, $config);
    }

    /**
     * @param array $settings
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function _beforeSuite($settings = [])
    {
        parent::_beforeSuite($settings);

        if ($this->config['run_installer'] === true) {
            $this->installBundle($settings);
        }
    }

    /**
     * @param $settings
     *
     * @throws \Codeception\Exception\ModuleException
     */
    private function installBundle($settings)
    {
        /** @var PimcoreCore $pimcoreModule */
        $pimcoreModule = $this->getModule('\\' . PimcoreCore::class);

        $bundleName = getenv('DACHCOM_BUNDLE_NAME');
        $installerClass = getenv('DACHCOM_BUNDLE_INSTALLER_CLASS');

        if ($installerClass === false) {
            return;
        }

        $this->debug(sprintf('[%s] Running installer...', strtoupper($bundleName)));

        if ($pimcoreModule->_getContainer()) {
            $pimcoreModule->getKernel()->reboot($pimcoreModule->getKernel()->getCacheDir());
        }

        // install dachcom bundle
        $installer = $pimcoreModule->getContainer()->get($installerClass);
        $installer->install();
    }
}
