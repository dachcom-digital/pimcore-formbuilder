<?php

class Formbuilder_Validate_Honeypot extends \Zend_Validate_Abstract
{
    const SPAM = 'spam';

    protected $_messageTemplates = [
        self::SPAM => 'I think you\'re a spambot. Sorry.'
    ];

    public function isValid($value, $context = NULL)
    {
        $value = (string)$value;
        $this->_setValue($value);

        if (is_string($value) && $value == '') {
            return TRUE;
        }

        $this->_error(self::SPAM);

        return FALSE;
    }
}