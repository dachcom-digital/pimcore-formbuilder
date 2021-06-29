<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Model\Document;
use Pimcore\Model\Site;
use Pimcore\Tool;

class PresetManager
{
    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getAll(Document $document): array
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
                    $allowedSites = (array) $presetConfig['sites'];

                    if (!in_array($currentSite->getMainDomain(), $allowedSites)) {
                        continue;
                    }
                }
            }

            $dat[$presetName] = $presetConfig;
        }

        return $dat;
    }

    public function getDataForPreview(string $presetName, array $presetConfig): array
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

    private function getCurrentSiteInAdminMode(Document $originDocument): ?Site
    {
        $currentSite = null;

        $site = Tool\Frontend::getSiteForDocument($originDocument);
        if ($site instanceof Site) {
            $currentSite = $site;
        }

        return $currentSite;
    }
}
