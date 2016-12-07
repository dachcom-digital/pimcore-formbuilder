<?php

namespace Formbuilder\Zend\Form\Element\Html5Text;

class Month extends \Formbuilder\Zend\Form\Element\Html5Text
{
    public function init()
    {
        if ($this->isAutoloadValidators())
        {
            //@todo: base month numbers on Zend_Locale
            $this->addValidator('Between', false, array('min' => 1, 'max' => 52));
        }
    }
}