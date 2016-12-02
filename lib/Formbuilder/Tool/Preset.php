<?php

namespace Formbuilder\Tool;

use Formbuilder\Model\Configuration;

class Preset {

    public static function getPresetConfig( $preset )
    {
        $formPresets = Configuration::get('form.area.presets');

        $dat = [];

        foreach( $formPresets as $presetName => $presetConfig )
        {
            if( $presetName === $preset)
            {
                $dat = $presetConfig;
                break;
            }
        }

        return $dat;

    }
}