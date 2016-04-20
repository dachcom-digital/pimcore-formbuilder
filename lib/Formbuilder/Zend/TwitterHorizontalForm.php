<?php

namespace Formbuilder\Zend;

use Formbuilder\Zend\Traits\Form;

class TwitterHorizontalForm extends \Twitter_Bootstrap3_Form_Horizontal {

    use Form;

    public function __construct( $formData )
    {
        $this->addPrefixes();
        parent::__construct($formData);
    }

}