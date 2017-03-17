<?php

namespace Formbuilder\Zend\Form\Element;

class Notice extends \Zend_Form_Element_Text
{
    /** @var string specify formNotice helper */
    public $helper = 'formNotice';

    /**
     * Constructor for element and adds validator
     *
     * @param array|string|Zend_Config $spec
     * @param null                     $options
     *
     * @throws \Zend_Exception
     * @throws \Zend_Form_Exception
     */
    public function __construct($spec, $options = NULL)
    {
        parent::__construct($spec, $options);
        $this->removeDecorator('Label');
    }

}
