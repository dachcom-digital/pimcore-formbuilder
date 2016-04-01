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
 * Renders an element main container
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_Container extends Zend_Form_Decorator_HtmlTag
{
    /**
     * Snippets for positioning before content
     * @var array
     */
    protected $_beforeContent = array();
    
    /**
     * Snippets for positioning after content
     * @var array
     */
    protected $_afterContent = array();
    
    /**
     * Decorate content and/or element
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $warnings = $element->getAttrib('warning');
        $dimension = $element->getAttrib('dimension');
        
        $class = ' ' . $this->getOption('class');
        $class .= ' form-group';
        
        if ($element->hasErrors()) {
            $class .= ' has-error';
        } elseif (!empty($warnings)) {
            $class .= ' has-warning';
        } elseif (true === $element->getAttrib('success')) {
            $class .= ' has-success';
        }
        
        $class = trim($class);
        if (!empty($class)) {
            $this->setOption('class', $class);
        }
        
        $before = implode('', $this->_beforeContent);
        $after = implode('', $this->_afterContent);
        return parent::render($before . $content . $after);
    }
    
    /**
     * Add HTML fragment to position before content
     * 
     * @param type $html
     * @return Twitter_Bootstrap3_Form_Decorator_Container
     */
    public function addBeforeContent($html)
    {
        $this->_beforeContent[] = $html;
        return $this;
    }
    
    /**
     * Add HTML fragment to position after content
     * 
     * @param type $html
     * @return Twitter_Bootstrap3_Form_Decorator_Container
     */
    public function addAfterContent($html)
    {
        $this->_afterContent[] = $html;
        return $this;
    }
    
    /**
     * Clear all registered HTML fragments to position before content
     * 
     * @return Twitter_Bootstrap3_Form_Decorator_Container
     */
    public function clearBeforeContent()
    {
        $this->_beforeContent = array();
        return $this;
    }
    
    /**
     * Clear all registered HTML fragments to position after content
     * 
     * @return Twitter_Bootstrap3_Form_Decorator_Container
     */
    public function clearAfterContent()
    {
        $this->_afterContent = array();
        return $this;
    }
}
