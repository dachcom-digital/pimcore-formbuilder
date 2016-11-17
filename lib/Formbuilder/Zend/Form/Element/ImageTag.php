<?php

namespace Formbuilder\Zend\Form\Element;

class ImageTag extends \Zend_Form_Element
{
    /** @var string specify Html5_file helper */
    public $helper = 'formImageTag';

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
        parent::__construct($spec, $options);

        $this->removeDecorator('Label');
    }
}
