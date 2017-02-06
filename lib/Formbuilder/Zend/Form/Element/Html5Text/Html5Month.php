<?php

namespace Formbuilder\Zend\Form\Element\Html5Text;

class Html5Month extends \Formbuilder\Zend\Form\Element\Html5Text
{
    public function init()
    {
        if ($this->isAutoloadValidators()) {
            //@todo: base month numbers on Zend_Locale
            $this->addValidator('Between', FALSE, ['min' => 1, 'max' => 52]);
        }
    }
}