<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Tool;

class PresetManager
{
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $areaConfig = $this->configuration->getConfig('area');
        return $areaConfig['presets'];
    }

    /**
     * @return array
     */
    public function getAvailablePresets()
    {
        $formPresets = $this->getAll();

        $dat = [];

        if (empty($formPresets)) {
            return $dat;
        }

        foreach ($formPresets as $presetName => $presetConfig) {
            //check for site restriction
            if (Tool::isFrontendRequestByAdmin() && isset($presetConfig['site']) && !empty($presetConfig['site'])) {
                $currentSite = $this->getCurrentSiteInAdminMode();

                if ($currentSite !== NULL) {
                    $allowedSites = (array)$presetConfig['site'];

                    if (!in_array($currentSite->getMainDomain(), $allowedSites)) {
                        continue;
                    }
                }
            }

            $dat[$presetName] = $presetConfig;
        }

        return $dat;
    }

    /**
     * @param $presetName
     * @param $presetConfig
     *
     * @return array
     */
    public function getDataForPreview($presetName, $presetConfig)
    {
        $previewData = ['presetName' => $presetName, 'description' => '', 'fields' => []];

        if (isset($presetConfig['adminDescription'])) {
            $previewData['description'] = strip_tags($presetConfig['adminDescription'], '<br><strong><em><p><span>');
        }

        return $previewData;
    }

    /**
     * Get Site Id in EditMode if SiteRequest is available
     * @return null|\Pimcore\Model\Site
     */
    private function getCurrentSiteInAdminMode()
    {
        $front = \Zend_Controller_Front::getInstance();
        $originDocument = $front->getRequest()->getParam('document');

        $currentSite = NULL;

        if ($originDocument) {
            $site = Tool\Frontend::getSiteForDocument($originDocument);

            if ($site) {
                $siteId = $site->getId();

                if ($siteId !== NULL) {
                    $currentSite = $site;
                }
            }
        }

        return $currentSite;
    }
}
