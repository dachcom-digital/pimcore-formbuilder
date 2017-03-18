<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class Image extends MapAbstract
{
    /**
     * @param array  $element
     * @param string $formType
     * @param array  $formInfo

     * @return array
     */
    public static function parse($element = [], $formType = '', $formInfo = [])
    {
        if (!isset($element['options']['useAsInputField']) || (int)$element['options']['useAsInputField'] !== 1) {
            $element['type'] = 'imageTag';
        }

        return $element;
    }
}