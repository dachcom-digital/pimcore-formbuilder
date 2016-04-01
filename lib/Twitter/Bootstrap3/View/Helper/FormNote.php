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
 * Helper to show an HTML note
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_View
 * @subpackage Helper
 */
class Twitter_Bootstrap3_View_Helper_FormNote extends Zend_View_Helper_FormNote
{
    /**
     * Helper to show a "note" based on a hidden value.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param array $value The note to display.  HTML is *not* escaped; the
     * note is displayed as-is.
     *
     * @return string The element XHTML.
     */
    public function formNote($name, $value = null, $attribs = null)
    {
        if (array_key_exists('class', $attribs)) {
            $classes = explode(' ', $attribs['class']);
            if (!in_array('form-control-static', $classes)) {
                $attribs['class'] = 'form-control-static ' . implode(' ', $classes);
            }
        } else {
            $attribs['class'] = 'form-control-static';
        }
        
        $escape = array_key_exists('escape', $attribs) ? (bool) $attribs['escape'] : true;
        $value = ($escape) ? $this->view->escape($value) : $value;
        
        $xhtml = '<p'
               . $this->_htmlAttribs($attribs) . '>'
               . $value . '</p>';

        return $xhtml;
    }
}
