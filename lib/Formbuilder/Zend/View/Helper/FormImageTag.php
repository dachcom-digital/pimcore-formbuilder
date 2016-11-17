<?php

namespace Formbuilder\Zend\View\Helper;

class FormImageTag extends \Zend_View_Helper_FormElement
{
    public function formImageTag($name, $value = null, $attribs = null, $options = null, $listsep = '')
    {
        $info = $this->_getInfo($name, $value, $attribs);

        if( isset( $attribs['useAsInputField'] ) )
        {
            unset( $attribs['useAsInputField'] );
        }

        if( isset( $attribs['image'] ) )
        {
            $attribs['src'] = $attribs['image'];
            unset( $attribs['image'] );
        }

        $xHtml = '<img'
            . $this->_htmlAttribs($attribs )
            . '>';

        return $xHtml;
    }
}