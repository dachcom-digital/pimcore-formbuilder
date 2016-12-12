<?php

namespace Formbuilder\Zend\Form\Element;

class Html5File extends \Zend_Form_Element
{
    /** @var string specify Html5_file helper */
    public $helper = 'formHtml5File';

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

        parent::__construct($spec, $options);
    }
}
