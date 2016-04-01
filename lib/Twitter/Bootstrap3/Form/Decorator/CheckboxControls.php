<?php
/**
 * Twitter Bootstrap v.3 Form for Zend Framework v.1
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 * @author Ilya Serdyuk <ilya.serdyuk@youini.org>
 */

/**
 * Renders an element checkbox controls container
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_CheckboxControls extends Zend_Form_Decorator_HtmlTag
{
    /**
     * Decorate content and/or element
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $tag = $this->getTag();
        $attribs = $this->getOptions();
        $element = $this->getElement();
        
        if (array_key_exists('class', $attribs) && is_string($attribs['class']) && !empty($attribs['class'])) {
            if (!in_array('checkbox', explode(' ', $attribs['class']))) {
                $attribs['class'] = 'checkbox ' . $attribs['class'];
            }
        } else {
            $attribs['class'] = 'checkbox';
        }
        
        $disabled = $element->getAttrib('disabled');
        if ($disabled) {
            $attribs['class'] .= ' disabled';
        }
        
        $xhtml = $this->_getOpenTag($tag, $attribs)
               . $content
               . $this->_getCloseTag($tag);
        
        return $xhtml;
    }
}
