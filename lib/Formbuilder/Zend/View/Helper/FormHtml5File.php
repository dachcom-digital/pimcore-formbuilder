<?php

namespace Formbuilder\Zend\View\Helper;

class FormHtml5File extends \Zend_View_Helper_FormElement
{
    public function formHtml5File($name, $value = null, $attribs = null, $options = null, $listsep = '')
    {
        return $this->view->render('formbuilder/form/elements/html5file/default.php');
    }

}