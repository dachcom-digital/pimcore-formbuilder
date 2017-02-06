<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

abstract class MapAbstract
{
    /**
     * @param array  $element
     * @param string $formType
     *
     * @return array
     */
    public static function parse($element = [], $formType = '')
    {
        return $element;
    }
}