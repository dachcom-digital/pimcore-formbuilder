<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class Hash extends MapAbstract
{
    /**
     * @param array  $element
     * @param string $formType
     * @param array  $formInfo
     *
     * @return array
     */
    public static function parse($element = [], $formType = '', $formInfo = [])
    {
        if ($formInfo['useAjax'] === TRUE) {
            $element['options']['ajax'] = TRUE;
        }

        return $element;
    }
}