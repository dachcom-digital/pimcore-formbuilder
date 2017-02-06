<?php

namespace Formbuilder\Zend\View\Helper;

class FormImageTag extends \Zend_View_Helper_FormElement
{
    /**
     * @param        $name
     * @param null   $value
     * @param null   $attribs
     * @param null   $options
     * @param string $listsep
     *
     * @return string
     */
    public function formImageTag($name, $value = NULL, $attribs = NULL, $options = NULL, $listsep = '')
    {
        $info = $this->_getInfo($name, $value, $attribs);

        if (isset($attribs['useAsInputField'])) {
            unset($attribs['useAsInputField']);
        }

        if (isset($attribs['image'])) {
            $attribs['src'] = $attribs['image'];
            unset($attribs['image']);
        }

        $xHtml = '<img'
            . $this->_htmlAttribs($attribs)
            . '>';

        return $xHtml;
    }
}