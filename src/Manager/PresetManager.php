<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Model\Document;
use Pimcore\Model\Site;
use Pimcore\Tool;

class PresetManager
{
    public function __construct(protected Configuration $configuration)
    {
    }

    public function getAll(Document\PageSnippet $document): array
    {
        $areaConfig = $this->configuration->getConfig('area');
        $formPresets = $areaConfig['presets'];

        $data = [];

        if (empty($formPresets)) {
            return $data;
        }

        foreach ($formPresets as $presetName => $presetConfig) {
            //check for site restriction
            if (Tool::isFrontendRequestByAdmin() && isset($presetConfig['sites']) && !empty($presetConfig['sites'])) {
                $currentSite = $this->getCurrentSiteInAdminMode($document);

                if ($currentSite !== null) {
                    $allowedSites = (array) $presetConfig['sites'];

                    if (!in_array($currentSite->getMainDomain(), $allowedSites, true)) {
                        continue;
                    }
                }
            }

            $data[$presetName] = $presetConfig;
        }

        return $data;
    }

    public function getDataForPreview(string $presetName): array
    {
        $areaConfig = $this->configuration->getConfig('area');
        $formPresets = $areaConfig['presets'];

        $previewData = [
            'presetName'  => $presetName,
            'description' => '',
            'fields'      => []
        ];

        if (!is_array($formPresets) || !array_key_exists($presetName, $formPresets)) {
            return $previewData;
        }

        $presetConfig = $formPresets[$presetName];

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
