<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Model\Document;
use Pimcore\Tool;

class PresetManager
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * PresetManager constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param Document $document
     *
     * @return array
     */
    public function getAll(Document $document)
    {
        $areaConfig = $this->configuration->getConfig('area');
        $formPresets = $areaConfig['presets'];

        $dat = [];

        if (empty($formPresets)) {
            return $dat;
        }

        foreach ($formPresets as $presetName => $presetConfig) {
            //check for site restriction
            if (Tool::isFrontendRequestByAdmin() && isset($presetConfig['sites']) && !empty($presetConfig['sites'])) {
                $currentSite = $this->getCurrentSiteInAdminMode($document);

                if ($currentSite !== null) {
                    $allowedSites = (array)$presetConfig['sites'];

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
        $previewData = [
            'presetName'  => $presetName,
            'description' => '',
            'fields'      => []
        ];

        if (isset($presetConfig['admin_description'])) {
            $previewData['description'] = strip_tags($presetConfig['admin_description'], '<br><strong><em><p><span>');
        }

        return $previewData;
    }

    /**
     * Get Site Id in EditMode if SiteRequest is available
     *
     * @param Document $originDocument
     * @return null|\Pimcore\Model\Site
     */
    private function getCurrentSiteInAdminMode($originDocument)
    {
        $currentSite = null;

        if ($originDocument) {
            $site = Tool\Frontend::getSiteForDocument($originDocument);
            if ($site) {
                $siteId = $site->getId();
                if ($siteId !== null) {
                    $currentSite = $site;
                }
            }
        }

        return $currentSite;
    }
}
