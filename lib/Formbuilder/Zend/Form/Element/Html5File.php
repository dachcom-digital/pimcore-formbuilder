<?php

namespace Formbuilder\Zend\Form\Element;

class Html5File extends \Zend_Form_Element
{
    /** @var string specify Html5_file helper */
    public $helper = 'formHtml5File';

    /**
     * @var null
     */
    private $formId = NULL;

    /**
     * Constructor for element and adds validator
     *
     * @param array|string|Zend_Config $spec
     * @param null $options
     * @throws \Zend_Exception
     * @throws \Zend_Form_Exception
     */
    public function __construct($spec, $options = null)
    {
        //keep nice name in subForms!
        $options['realName'] = $spec;
        $options['uploadIsRequired'] = isset( $options['required'] ) && $options['required'] === TRUE;

        if( isset( $options['formId']))
        {
            $this->formId = (int) $options['formId'];
        }

        parent::__construct($spec, $options);
    }

    public function isValid($value, $context = null)
    {
        if( is_null( $this->formId ) )
        {
            return TRUE;
        }

        if( !$this->isRequired() )
        {
            return TRUE;
        }

        $stored = \Formbuilder\Tool\Session::getFromTmpSession( $this->formId );

        if( empty( $stored ) || !isset( $stored[ $this->getName() ] ))
        {
            return parent::isValid( $value, $context );
        }

        return TRUE;
    }
}
