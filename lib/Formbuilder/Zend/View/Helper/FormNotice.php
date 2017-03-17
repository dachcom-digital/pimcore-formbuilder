<?php

namespace Formbuilder\Zend\View\Helper;

class FormNotice extends \Zend_View_Helper_FormElement
{
    /**
     * @param        $name
     * @param null   $value
     * @param null   $attribs
     * @param null   $options
     * @param string $listsep
     *
     * @return mixed
     */
    public function formNotice($name, $value = NULL, $attribs = NULL, $options = NULL, $listsep = '')
    {
        $content = isset($attribs['content']) ? $attribs['content'] : '';
        $translator = $this->getTranslator();

        if ($translator !== NULL) {
            $content = $translator->translate($content);
        }

        return $this->view->partial('formbuilder/form/elements/notice/default.php', [
            'content' => $content
        ]);
    }
}