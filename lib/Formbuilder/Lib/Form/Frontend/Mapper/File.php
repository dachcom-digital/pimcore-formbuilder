<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class File extends MapAbstract {

    /**
     * @param array $element
     * @param string $formType
     *
     * @return array
     */
    public static function parse( $element = [], $formType = '' )
    {
        $element['options']['destination'] = PIMCORE_WEBSITE_PATH . '/' . ltrim($element['options']['destination'] , '/');

        //if it's a multifile, use a javascript library!
        if( (int) $element['options']['multiFile'] === 1 )
        {
            $element['type'] = 'html5File';

            if( !isset( $element['options']['validators'] ) )
            {
                $element['options']['validators'] = [];
            }

            $element['options']['validators']['html5file'] = [
                'validator' => 'Html5File',
                'options'   => []
            ];

        }

        return $element;
    }
}