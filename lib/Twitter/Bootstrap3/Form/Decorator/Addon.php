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
 * Renders an form field with an add on (appended or prepended)
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_Addon extends Zend_Form_Decorator_Abstract
{
    /**
     * Support element types
     * @var array 
     */
    protected $_types = array(
        'text', 'password', 'dateTime', 'dateTimeLocal', 'date', 'month', 
        'time', 'week', 'number', 'email', 'url', 'search', 'tel', 'color',
    );
    
    /**
     * Append addon element
     * @var string
     */
    protected $_appendAddon;
    
    /**
     * Prepend addon element
     * @var string
     */
    protected $_prependAddon;
    
    /**
     * Decorate content and/or element
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $prependAddon = $this->getPrependAddon();
        $appendAddon = $this->getAppendAddon();
        
        if (empty($prependAddon) && empty($appendAddon)) {
            return $content;
        }
        
        if (!empty($prependAddon)) {
            $prependAddon = '<span class="input-group-addon">'
                          . $prependAddon . '</span>';
        }
        
        if (!empty($appendAddon)) {
            $appendAddon = '<span class="input-group-addon">'
                         . $appendAddon . '</span>';
        }
        
        $xhtml = '<div class="input-group">'
               . $prependAddon
               . $content
               . $appendAddon
               . '</div>';
        
        return $xhtml;
    }
    
    /**
     * Get prepend element addon
     * 
     * @return null|string
     */
    public function getPrependAddon()
    {
        $element = $this->getElement();
        if (null !== ($prepend = $this->getOption('prepend'))) {
            $this->_prependAddon = $prepend;
            $this->removeOption('prepend');
        } elseif (null !== ($prepend = $element->getAttrib('addon_prepend'))) {
            $this->_prependAddon = $prepend;
            $element->setAttrib('addon_prepend', null);
        }
        
        return $this->_prependAddon;
    }
    
    /**
     * Get append element addon
     * 
     * @return null|string
     */
    public function getAppendAddon()
    {
        $element = $this->getElement();
        if (null !== ($append = $this->getOption('append'))) {
            $this->_appendAddon = $append;
            $this->removeOption('append');
        } elseif (null !== ($append = $element->getAttrib('addon_append'))) {
            $this->_appendAddon = $append;
            $element->setAttrib('addon_append', null);
        }
        
        return $this->_appendAddon;
    }
}
