<?php
/**
 * Twitter Bootstrap v.3 Form for Zend Framework v.1
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_View
 * @subpackage Helper
 * @author Ilya Serdyuk <ilya.serdyuk@youini.org>
 */

/**
 * Helper to generate a "button" element
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_View
 * @subpackage Helper
 */
class Twitter_Bootstrap3_View_Helper_FormButton extends Zend_View_Helper_FormButton
{
    /**
     * Generates a 'button' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formButton($name, $value = null, $attribs = null)
    {
        if (isset($attribs['class'])) {
            $attribs['class'] = 'btn ' . $attribs['class'];
            $attribs['class'] = trim($attribs['class']);
        } else {
            $attribs['class'] = 'btn';
        }
        
        // Если кроме класса btn других нету, то надо добавить дополнительный стиль по умолчанию
        if ('btn' == $attribs['class']) {
            $attribs['class'] .= ' btn-default';
        }
        
        return parent::formButton($name, $value, $attribs);
    }
}
