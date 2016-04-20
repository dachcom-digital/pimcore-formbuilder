<?php

namespace Formbuilder\Zend;

use Formbuilder\Zend\Traits\Form;

class TwitterVerticalForm extends \Twitter_Bootstrap3_Form_Vertical {

    use Form;

    public function __construct( $formData )
    {
        $this->addPrefixes();
        parent::__construct($formData);
    }

}