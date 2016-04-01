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
 * Helper to render errors for a form element
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_View
 * @subpackage Helper
 */
class Twitter_Bootstrap3_View_Helper_FormErrors extends Zend_View_Helper_FormErrors
{
    /**
     * Render form errors
     *
     * @param  string|array $errors Error(s) to render
     * @param  array $options
     * @return string
     */
    public function formErrors($errors, array $options = null)
    {
        if (empty($options['class'])) {
            $options['class'] = 'help-block';
        }
        
        return parent::formErrors($errors, $options);
    }
}
