<?php

namespace Formbuilder\Zend\Form\Element\Html5Text;

class Email extends \Formbuilder\Zend\Form\Element\Html5Text
{
    public function init()
    {
        if ($this->isAutoloadValidators())
        {
            $this->addValidator('EmailAddress');
        }
    }

}