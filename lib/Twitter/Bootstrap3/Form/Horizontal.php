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
 * An "horizontal" Twitter Bootstrap's UI form
 * 
 * @category Forms
 * @package Twitter_Bootstrap3
 * @subpackage Form
 */
class Twitter_Bootstrap3_Form_Horizontal extends Twitter_Bootstrap3_Form
{
    /**
     * Disposition
     * @var integer 
     */
    protected $_disposition = self::DISPOSITION_HORIZONTAL;
    
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
            array('Errors'),
            array('Description', array(
                'tag' => 'p',
                'class' => 'help-block',
            )),
            array('HorizontalControls'),
            array('HorizontalLabel'),
            array('Container'),
            array('FieldSize'),
        );
    }
    
    /**
     * Retrieve all decorators for all captcha elements
     * 
     * @return array
     */
    public function getDefaultCaptchaDecorators()
    {
        return array(
            array('ViewHelper'),
            array('Addon'),
            array('Errors'),
            array('Description', array(
                'tag' => 'p',
                'class' => 'help-block',
            )),
            array('HorizontalControls'),
            array('HorizontalLabel'),
            array('Container'),
            array('FieldSize'),
        );
    }
    
    /**
     * Retrieve all decorators for all checkbox elements
     * 
     * @return array
     */
    public function getDefaultCheckboxDecorators()
    {
        return array(
            array('ViewHelper'),
            array('CheckboxLabel'),
            array('Errors'),
            array('Description', array(
                'tag' => 'p',
                'class' => 'help-block',
            )),
            array('CheckboxControls'),
            array('HorizontalControls', array(
                'noLabel' => true,
            )),
            array('Container'),
            array('FieldSize'),
        );
    }
    
    /**
     * Retrieve all decorators for all elements types: button, submit and reset
     * 
     * @return array
     */
    public function getDefaultButtonsDecorators()
    {
        return array(
            array('Tooltip'),
            array('Description', array(
                'tag' => 'p',
                'class' => 'help-block',
            )),
            array('ViewHelper'),
            array('HorizontalControls', array(
                'noLabel' => true,
            )),
            array('Container'),
            array('FieldSize'),
        );
    }
    
    /**
     * Retrieve all decorators for all elements type image
     * 
     * @return array
     */
    public function getDefaultImageDecorators()
    {
        return array(
            array('Tooltip'),
            array('Description', array(
                'tag' => 'p',
                'class' => 'help-block',
            )),
            array('Image'),
            array('Errors'),
            array('HorizontalControls', array(
                'noLabel' => true,
            )),
            array('Container'),
            array('FieldSize'),
        );
    }
}
