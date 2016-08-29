<?php

namespace Formbuilder\Zend\Traits;

trait Form
{
    /**
     * @return $this
     */
    protected function addPrefixes()
    {
        $recaptchaPath = realpath(PIMCORE_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src');

        if (null !== $this->getView()) {
            $this->getView()->addHelperPath($recaptchaPath . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
            $this->getView()->headScript()->appendFile('//www.google.com/recaptcha/api.js?hl=' . $this->getView()->language);
        }

        $this->addPrefixPath(
            'Cgsmith\\Form\\Element',
            $recaptchaPath . '/Cgsmith/Form/Element/',
            \Zend_Form::ELEMENT
        );

        $this->addElementPrefixPath(
            'Cgsmith\\Validate\\',
            $recaptchaPath . '/Cgsmith/Validate/',
            \Zend_Form_Element::VALIDATE
        );

        $this->addElementPrefixPath(
            'Formbuilder',
            'Formbuilder/Zend/Form/'
        );

        return $this;

    }

    /**
     * Validate the form
     *
     * This Method is a clone from the original \Zend_Form->isValid() Method. Because the google reCaptcha only allows one-time-success,
     * we need to suppress the captcha validation, until all other fields are ok.
     *
     * @param  array $data
     * @param  null|string $suppressCaptchaValidation
     * @throws \Zend_Form_Exception
     * @return bool
     */
    public function isValid($data, $suppressCaptchaValidation = NULL)
    {
        if (!is_array($data)) {
            throw new \Zend_Form_Exception(__METHOD__ . ' expects an array');
        }

        $translator = $this->getTranslator();
        $valid      = true;
        $eBelongTo  = null;

        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
            $data = $this->_dissolveArrayValue($data, $eBelongTo);
        }
        $context = $data;
        /** @var \Zend_Form_Element $element */
        foreach ($this->getElements() as $key => $element) {

            if( !is_null( $suppressCaptchaValidation ) && $key == $suppressCaptchaValidation)
            {
                continue;
            }

            if (null !== $translator && $this->hasTranslator()
                && !$element->hasTranslator()) {
                $element->setTranslator($translator);
            }
            $check = $data;
            if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                $check = $this->_dissolveArrayValue($data, $belongsTo);
            }
            if (!isset($check[$key])) {
                $valid = $element->isValid(null, $context) && $valid;
            } else {
                $valid = $element->isValid($check[$key], $context) && $valid;
                $data = $this->_dissolveArrayUnsetKey($data, $belongsTo, $key);
            }
        }
        /** @var \Zend_Form_SubForm $form */
        foreach ($this->getSubForms() as $key => $form) {
            if (null !== $translator && $this->hasTranslator()
                && !$form->hasTranslator()) {
                $form->setTranslator($translator);
            }
            if (isset($data[$key]) && !$form->isArray()) {
                $valid = $form->isValid($data[$key]) && $valid;
            } else {
                $valid = $form->isValid($data) && $valid;
            }
        }

        $this->_errorsExist = !$valid;

        if ($this->_errorsForced) {
            return false;
        }

        return $valid;
    }


}

