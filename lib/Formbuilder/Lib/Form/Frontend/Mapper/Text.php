<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class Text extends MapAbstract
{
    /**
     * @param array  $element
     * @param string $formType
     * @param array  $formInfo

     * @return array
     */
    public static function parse($element = [], $formType = '', $formInfo = [])
    {
        if (!self::isHtml5Element($element)) {
            return $element;
        }

        $element['type'] = self::getTypeName($element['options']['inputType']);
        $element['options']['inputType'] = $element['type'];

        //set extended attributes
        if (isset($element['options']['html5Options'])) {
            $options = $element['options']['html5Options'];
            unset($element['options']['html5Options']);

            if (is_array($options) && !empty($options)) {
                $element['options'] = array_merge($element['options'], $options);
            }
        }

        return $element;
    }

    /**
     * @param $element
     *
     * @return bool
     */
    private static function isHtml5Element($element)
    {
        return isset($element['options']['inputType'])
            && !empty($element['options']['inputType'])
            && $element['options']['inputType'] !== 'default';
    }

    /**
     * @param $name
     *
     * @return string
     */
    private static function getTypeName($name)
    {
        $parts = explode('-', $name);
        $parts = array_map('ucfirst', $parts);
        $name = lcfirst(implode('', $parts));

        return 'Html5' . ucfirst($name);
    }
}