<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Symfony\Component\Filesystem\Filesystem;

class BundleStructure extends Module
{
    /**
     * @inheritDoc
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        $this->config = array_merge($this->config, [
            'run_installer'             => false,
            'run_template_configurator' => false
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
        if ($this->config['run_installer'] === true) {
            $this->installBundle($settings);
        }

        if ($this->config['run_template_configurator'] === true) {
            $this->installTemplates($settings);
        }

    }

    public function _afterSuite()
    {
        if ($this->config['run_template_configurator'] === true) {
            $this->removeTemplates();
        }
    }

    /**
     * @param $settings
     *
     * @throws \Codeception\Exception\ModuleException
     */
    private function installBundle($settings)
    {
        /** @var PimcoreBundle $pimcoreModule */
        $pimcoreModule = $this->getModule('\\' . PimcoreBundle::class);

        $bundleName = getenv('DACHCOM_BUNDLE_NAME');
        $installerClass = getenv('DACHCOM_BUNDLE_INSTALLER_CLASS');

        if ($installerClass === false) {
            return;
        }

        $this->debug(sprintf('[%s] Running installer...', strtoupper($bundleName)));

        // install dachcom bundle
        $installer = $pimcoreModule->getContainer()->get($installerClass);
        $installer->install();
    }

    /**
     * @param $settings
     */
    private function installTemplates($settings)
    {
        $bundleName = getenv('DACHCOM_BUNDLE_NAME');
        $this->debug(sprintf('[%s] Install Bundle Templates...', strtoupper($bundleName)));

        $fileSystem = new Filesystem();
        foreach ($this->getTemplateFiles() as $templateSource => $templateDest) {
            $this->debug(sprintf('[%s] Copy Bundle Template %s to %s.', strtoupper($bundleName), $templateSource, $templateDest));
            $fileSystem->copy($templateSource, $templateDest);
        }

        $fileSystem->remove(PIMCORE_PROJECT_ROOT . '/var/cache');
        $fileSystem->mkdir(PIMCORE_PROJECT_ROOT . '/var/cache');
    }

    private function removeTemplates()
    {
        $bundleName = getenv('DACHCOM_BUNDLE_NAME');
        $this->debug(sprintf('[%s] Install Bundle Templates...', strtoupper($bundleName)));

        $fileSystem = new Filesystem();
        foreach ($this->getTemplateFiles() as $templateSource => $templateDest) {
            if ($fileSystem->exists($templateDest)) {
                $this->debug(sprintf('[%s] Removing Bundle Template %s', strtoupper($bundleName), $templateDest));
                $fileSystem->remove($templateDest);
            }
        }
    }

    /**
     * @return array
     */
    private function getTemplateFiles()
    {
        $bundleClass = getenv('DACHCOM_BUNDLE_HOME');
        $templatePath = $bundleClass . '/etc/config/bundle/template';

        return [
            $templatePath . '/controller/DefaultController' => PIMCORE_PROJECT_ROOT . '/src/AppBundle/Controller/DefaultController.php',
            $templatePath . '/views/default'                => PIMCORE_APP_ROOT . '/Resources/views/Default/default.html.twig'

        ];
    }
}
