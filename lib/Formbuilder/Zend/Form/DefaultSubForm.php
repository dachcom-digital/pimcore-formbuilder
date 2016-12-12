<?php

namespace Formbuilder\Zend\Form;

use Formbuilder\Zend\Traits\Form;

class DefaultSubForm extends \Zend_Form_SubForm {

    use Form;

    public function __construct( $formData )
    {
        $this->addPrefixes();
        parent::__construct($formData);
    }
}