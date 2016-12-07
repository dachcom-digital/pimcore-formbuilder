<?php

namespace Formbuilder\Zend\Form\Element\Html5Text;

class DateTimeLocal extends \Formbuilder\Zend\Form\Element\Html5Text
{
    public function __construct($spec, $options = null)
    {
        $options['type'] = 'datetime-local';

        parent::__construct($spec, $options);
    }
}