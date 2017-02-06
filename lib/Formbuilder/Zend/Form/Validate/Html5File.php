<?php

class Formbuilder_Validate_Html5File extends \Zend_Validate_Abstract
{
    /**
     * Validate our form's element
     *
     * @param mixed $value
     * @param null  $context
     *
     * @return bool
     */
    public function isValid($value, $context = NULL)
    {
        return TRUE;
    }
}