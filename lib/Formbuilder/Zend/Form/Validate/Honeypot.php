<?php

class Formbuilder_Validate_Honeypot extends Zend_Validate_Abstract
{
    const SPAM = 'spam';

    protected $_messageTemplates = array(
        self::SPAM => "I think you're a spambot. Sorry."
    );

    public function isValid($value, $context = null)
    {
        $value = (string)$value;
        $this->_setValue($value);

        if(is_string($value) and $value == ''){
            return true;
        }

        $this->_error(self::SPAM);
        return false;
    }
}