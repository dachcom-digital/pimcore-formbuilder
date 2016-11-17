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

    /**
     * Retrieve a registered decorator for type element
     *
     * @param  string $type
     * @return array
     */
    public function getDefaultDecoratorsByElementType($type)
    {
        if( in_array($type, array('download', 'html5File', 'imageTag') ) )
        {
            return parent::getDefaultDecoratorsByElementType('text');
        }

        return parent::getDefaultDecoratorsByElementType($type);
    }
}