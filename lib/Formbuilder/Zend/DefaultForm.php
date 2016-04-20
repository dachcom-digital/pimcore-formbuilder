<?php

namespace Formbuilder\Zend;

use Formbuilder\Zend\Traits\Form;

class DefaultForm extends \Zend_Form {

    use Form;

    public function __construct( $formData )
    {
        $this->addPrefixes();
        parent::__construct($formData);
    }

}