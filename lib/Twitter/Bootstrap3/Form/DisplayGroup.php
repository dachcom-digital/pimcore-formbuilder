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
 * Displays the fieldsets the Bootstrap's way
 * 
 * @category Forms
 * @package Twitter_Bootstrap3
 * @subpackage Form
 */
class Twitter_Bootstrap3_Form_DisplayGroup extends Zend_Form_DisplayGroup
{
    /**
     * Override the default decorators
     *
     * @return Twitter_Bootstrap3_Form_DisplayGroup
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('Fieldset');
        }
        
        return $this;
    }
}
