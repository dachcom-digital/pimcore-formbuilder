<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class Select extends MapAbstract {

    /**
     * @param array $element
     * @param string $formType
     *
     * @return array
     */
    public static function parse( $element = [], $formType = '' )
    {
        if( isset( $element['options']['multiOptions'] ) )
        {
            $realOptions = [];

            foreach( $element['options']['multiOptions'] as $optionKey => $optionValue)
            {
                if( $optionKey === 'choose')
                {
                    $optionKey = '';
                }

                $realOptions[$optionKey] = $optionValue;

            }

            $element['options']['multiOptions'] = $realOptions;
        }

        return $element;
    }

}