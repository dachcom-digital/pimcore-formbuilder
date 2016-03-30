<?php

class Formbuilder_Decorator_Divctr extends Zend_Form_Decorator_Abstract {

    public function buildLabel($content)
    {
        $dec = new Zend_Form_Decorator_Label();
        $dec->setElement($this->getElement());
        return $dec->render($content);
    }

    public function buildInput($content)
    {
        $dec = new Zend_Form_Decorator_ViewHelper();
        $dec->setElement($this->getElement());
        return $dec->render($content);
    }

    public function buildErrors($content)
    {
        $element = $this->getElement();
        $messages = $element->getMessages();
        if (empty($messages))
        {
            return '';
        }
        $dec = new Zend_Form_Decorator_Errors();
        $dec->setElement($element);
        return $dec->render($content);
    }

    public function buildDescription()
    {
        $dec = new Zend_Form_Decorator_Description(array(
            'tag' => 'p',
            'class' => 'description'
        ));
        $dec->setElement($this->getElement());
        return $dec->render($content);
    }

    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element)
        {
            return $content;
        }
        if (NULL === $element->getView())
        {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $label = $this->buildLabel($content);
        $input = $this->buildInput($content);
        $errors = $this->buildErrors($content);
        $desc = $this->buildDescription($content);

        $output = '<div class="form element">' . $label . $input . $errors . $desc . '</div>';

        switch ($placement)
        {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }
    }
}