<?php

namespace Formbuilder\Tool;

use Pimcore\Tool as PimcoreTool;
use Pimcore\Model\Site;
use Formbuilder\Model\Configuration;

class Preset {

    public static function getAvailablePresets()
    {
        $formPresets = Configuration::get('form.area.presets');

        $dat = [];

        if( empty( $formPresets ) )
        {
            return $dat;
        }

        foreach( $formPresets as $presetName => $presetConfig )
        {
            //check for site restriction
            if( PimcoreTool::isFrontentRequestByAdmin() && isset( $presetConfig['site'] ) && !empty( $presetConfig['site'] ) )
            {
                $currentSite = self::getCurrentSiteInAdminMode();

                if( $currentSite !== NULL )
                {
                    $allowedSites = (array) $presetConfig['site'];

                    if( !in_array( $currentSite->getMainDomain(), $allowedSites ) )
                    {
                        continue;
                    }
                }
            }

            $dat[ $presetName ] = $presetConfig;
        }

        return $dat;

    }

    public static function getDataForPreview( $presetName, $presetConfig )
    {
        $previewData = [ 'presetName' => $presetName, 'description' => '', 'fields' => [] ];

        if( isset( $presetConfig['adminDescription'] ) )
        {
            $previewData['description'] = strip_tags($presetConfig['adminDescription'], '<br><strong><em><p><span>');
        }

        return $previewData;
    }

    /**
     * Get Site Id in EditMode if SiteRequest is available
     * @return null|\Pimcore\Model\Site
     */
    private static function getCurrentSiteInAdminMode()
    {
        $front = \Zend_Controller_Front::getInstance();
        $originDocument = $front->getRequest()->getParam('document');

        $currentSite = NULL;

        if ($originDocument)
        {
            $site = PimcoreTool\Frontend::getSiteForDocument($originDocument);

            if ($site)
            {
                $siteId = $site->getId();

                if( $siteId !== NULL )
                {
                    $currentSite = $site;
                }
            }
        }

        return $currentSite;
    }
}