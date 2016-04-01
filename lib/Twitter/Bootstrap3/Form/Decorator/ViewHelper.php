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
class Twitter_Bootstrap3_Form_Decorator_ViewHelper extends Zend_Form_Decorator_ViewHelper
{
    /**
     * Retrieve element attributes
     *
     * Set id to element name and/or array item.
     *
     * @return array
     */
    public function getElementAttribs()
    {
        $attribs = parent::getElementAttribs();
        
        unset($attribs['addon_append']);   // Twitter_Bootstrap3_Form_Decorator_Addon
        unset($attribs['addon_prepend']);  // Twitter_Bootstrap3_Form_Decorator_Addon
        unset($attribs['success']);        // Twitter_Bootstrap3_Form_Decorator_Container
        unset($attribs['warning']);        // Twitter_Bootstrap3_Form_Decorator_Container
        
        return $attribs;
    }
}
