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
 * Sets the class to its appropiate size
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_FieldSize extends Zend_Form_Decorator_HtmlTag
{
    
    /**
     * Controls container dimension
     * @var string 
     */
    protected $_dimension = null;
    
    /**
     * Render container to appropiate size
     * 
     * @param type $content
     * @return type
     */
    public function render($content)
    {
        $element = $this->getElement();
        $class = $this->getOption('class');
        $dimension = $this->getDimension();
        
        if (!empty($dimension)) {
            if (is_string($dimension)) {
                foreach (explode(',', $dimension) as $size) {
                    $class .= ' col-' . trim($size);
                }
            }
            
            $element->setAttrib('dimension', null);
        }
        
        $class = trim($class);
        if (!empty($class)) {
            $this->setOption('class', $class);
        } else {
            $this->removeOption('class');
        }
        
        
        $noAttribs = $this->getOption('noAttribs');
        if (!$noAttribs) {
            $attribs = $this->getOptions();
            if (count($attribs) > 0) {
                return parent::render($content);
            }
        }
        
        return $content;
    }
    
    /**
     * Get dimension
     * 
     * @return null|string
     */
    public function getDimension()
    {
        $element = $this->getElement();
        if (null !== ($dimension = $this->getOption('dimension'))) {
            $this->_dimension = $dimension;
            $this->removeOption('dimension');
        } elseif (null !== ($dimension = $element->getAttrib('dimension'))) {
            $this->_dimension = $dimension;
            $element->setAttrib('dimensionLabel', null);
        }
        
        return $this->_dimension;
    }
}
