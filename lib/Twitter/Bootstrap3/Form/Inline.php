<?php
/**
 * Twitter Bootstrap v.3 Form for Zend Framework v.1
 * 
 * @category Forms
 * @package Twitter_Bootstrap3
 * @subpackage Form
 * @author Ilya Serdyuk <ilya.serdyuk@youini.org>
 */

/**
 * An "inline" Twitter Bootstrap's UI form
 * 
 * @category Forms
 * @package Twitter_Bootstrap3
 * @subpackage Form
 */
class Twitter_Bootstrap3_Form_Inline extends Twitter_Bootstrap3_Form
{
    /**
     * Disposition
     * @var integer 
     */
    protected $_disposition = self::DISPOSITION_INLINE;
    
    /**
     * Retrieve all decorators for all simple type elements
     * 
     * @return array
     */
    public function getDefaultSimpleElementDecorators()
    {
        return array(
            array('ViewHelper'),
            array('Addon'),
            array('Feedback_State', array(
                'renderIcon' => $this->_renderElementsStateIcons,
                'successIcon' => $this->_elementsSuccessIcon,
                'warningIcon' => $this->_elementsWarningIcon,
                'errorIcon' => $this->_elementsErrorIcon,
            )),
            array('Label', array(
                'class' => 'sr-only',
            )),
            array('Container'),
        );
    }
}
