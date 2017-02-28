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
 * Renders an element checkbox label
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_CheckboxLabel extends Zend_Form_Decorator_HtmlTag
{
    /**
     * HTML tag to use
     * @var string
     */
    protected $_tag = 'label';
    
    /**
     * Decorate content and/or element
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $tag     = $this->getTag();
        $options = $this->getOptions();
        $element = $this->getElement();
        $elementId = $element->getId();

        //add for attribute
        if (!isset($options['for']) || empty($options['for'])) {
            $options['for'] = $elementId;
        }

        $xhtml = $this->_getOpenTag($tag, $options)
               . $content
               . $this->getSeparator()
               . $element->getLabel()
               . $this->_getCloseTag($tag);

        return $xhtml;
    }
}
