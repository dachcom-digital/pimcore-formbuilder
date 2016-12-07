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
 * Helper to generate a "text" element
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_View
 * @subpackage Helper
 */
class Twitter_Bootstrap3_View_Helper_FormText extends Zend_View_Helper_FormText
{
    /**
     * Array the specifies which types are allowed to be used for the type attribute
     *
     * @var array
     */
    protected $_allowedTypes = ['text', 'email', 'url', 'number', 'range', 'date', 'month', 'week', 'time', 'datetime-local'];

    /**
     * Generates a text|html5 element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formText($name, $value = null, $attribs = null)
    {
        $type = 'text';
        if (isset($attribs['type']) && in_array($attribs['type'], $this->_allowedTypes))
        {
            $type = $attribs['type'];
            unset($attribs['type']);
        }

        return $this->_formText($type, $name, $value, $attribs);
    }
    
    /**
     * Generates a custom input element
     *
     * @access public
     * 
     * @param string $type The element type
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    protected function _formText($type, $name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }
        
        $xhtml = '<input'
                . ' type="' . $this->view->escape($type) . '"'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . ' value="' . $this->view->escape($value) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs)
                . $this->getClosingBracket();

        return $xhtml;
    }
}
