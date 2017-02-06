<?php

namespace Formbuilder\Zend\Form\Element;

class Country extends \Zend_Form_Element_Select
{

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

        $list = \Zend_Locale::getTranslationList('territory', $this->getView()->language, 2);
        asort($list);

        $this->setMultiOptions(['' => ''] + $list);
    }
}
