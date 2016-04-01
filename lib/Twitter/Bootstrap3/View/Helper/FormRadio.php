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
 * Helper to generate a set of radio button elements
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_View
 * @subpackage Helper
 */
class Twitter_Bootstrap3_View_Helper_FormRadio extends Zend_View_Helper_FormRadio
{
    /**
     * Generates a set of radio button elements.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The radio value to mark as 'checked'.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param array|string $attribs Attributes added to each radio.
     *
     * @return string The radio buttons XHTML.
     */
    public function formRadio($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, value, attribs, options, listsep, disable
        
        $inline = false;
        if (array_key_exists('inline', $attribs)) {
            $inline = $attribs['inline'];
            unset($attribs['inline']);
        }
        $bClass = $this->_inputType;
        if ($inline) {
            $bClass .= '-inline';
            $bAttrib = 'label_class';
        } else {
            $bAttrib = 'wrapper_class';
        }
        if (array_key_exists($bAttrib, $attribs)) {
            if (!in_array($bClass, explode(' ', $attribs[$bAttrib]))) {
                $attribs[$bAttrib] = $bClass . ' ' . $attribs[$bAttrib];
            }
        } else {
            $attribs[$bAttrib] = $bClass;
        }
        
        // retrieve attributes for wrapper (prefixed with 'wrapper_' or 'wrapper')
        $wrapper_attribs = array();
        foreach ($attribs as $key => $val) {
            $tmp    = false;
            $keyLen = strlen($key);
            if ((8 < $keyLen) && (substr($key, 0, 8) == 'wrapper_')) {
                $tmp = substr($key, 8);
            } elseif ((7 < $keyLen) && (substr($key, 0, 7) == 'wrapper')) {
                $tmp = substr($key, 7);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0] = strtolower($tmp[0]);
                $wrapper_attribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        $label_attribs = array();
        foreach ($attribs as $key => $val) {
            $tmp    = false;
            $keyLen = strlen($key);
            if ((6 < $keyLen) && (substr($key, 0, 6) == 'label_')) {
                $tmp = substr($key, 6);
            } elseif ((5 < $keyLen) && (substr($key, 0, 5) == 'label')) {
                $tmp = substr($key, 5);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0] = strtolower($tmp[0]);
                $label_attribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

        $labelPlacement = 'append';
        foreach ($label_attribs as $key => $val) {
            switch (strtolower($key)) {
                case 'placement':
                    unset($label_attribs[$key]);
                    $val = strtolower($val);
                    if (in_array($val, array('prepend', 'append'))) {
                        $labelPlacement = $val;
                    }
                    break;
            }
        }

        // the radio button values and labels
        $options = (array) $options;

        // build the element
        $xhtml = '';
        $list  = array();

        // should the name affect an array collection?
        $name = $this->view->escape($name);
        if ($this->_isArray && ('[]' != substr($name, -2))) {
            $name .= '[]';
        }

        // ensure value is an array to allow matching multiple times
        $value = (array) $value;

        // Set up the filter - Alnum + hyphen + underscore
        require_once 'Zend/Filter/PregReplace.php';
        $pattern = @preg_match('/\pL/u', 'a') 
            ? '/[^\p{L}\p{N}\-\_]/u'    // Unicode
            : '/[^a-zA-Z0-9\-\_]/';     // No Unicode
        $filter = new Zend_Filter_PregReplace($pattern, "");
        
        // add radio buttons to the list.
        foreach ($options as $opt_value => $opt_label) {

            // Should the label be escaped?
            if ($escape) {
                $opt_label = $this->view->escape($opt_label);
            }

            // is it disabled?
            $disabled = '';
            if (true === $disable) {
                $disabled = ' disabled="disabled"';
            } elseif (is_array($disable) && in_array($opt_value, $disable)) {
                $disabled = ' disabled="disabled"';
            }
            if ('' != $disabled) {
                if ($inline) {
                    $label_attribs['class'] .= ' disabled';
                } else {
                    $wrapper_attribs['class'] .= ' disabled';
                }
            }

            // is it checked?
            $checked = '';
            if (in_array($opt_value, $value)) {
                $checked = ' checked="checked"';
            }

            // generate ID
            $optId = $id . '-' . $filter->filter($opt_value);

            // Wrap the radios in labels
            $radio = '<label'
                    . $this->_htmlAttribs($label_attribs) . '>'
                    . (('prepend' == $labelPlacement) ? $opt_label . ' ' : '')
                    . '<input type="' . $this->_inputType . '"'
                    . ' name="' . $name . '"'
                    . ' id="' . $optId . '"'
                    . ' value="' . $this->view->escape($opt_value) . '"'
                    . $checked
                    . $disabled
                    . $this->_htmlAttribs($attribs)
                    . $this->getClosingBracket()
                    . (('append' == $labelPlacement) ? ' ' . $opt_label : '')
                    . '</label>';
            
            if (0 < count($wrapper_attribs)) {
                $radio = '<div'
                       . $this->_htmlAttribs($wrapper_attribs) . '>'
                       . $radio
                       . '</div>';
            }
            
            // add to the array of radio buttons
            $list[] = $radio;
        }
        
        // XHTML or HTML for standard list separator?
        if (!$this->_isXhtml() && false !== strpos($listsep, '<br />')) {
            $listsep = str_replace('<br />', '<br>', $listsep);
        }

        // done!
        $xhtml .= implode($listsep, $list);
        
        return $xhtml;
    }
}
