<?php

namespace Formbuilder\Zend\Traits;

trait Form
{
    protected static $validFields = [
        'download',
        'html5file',
        'imagetag'
    ];

    protected static $validHtml5Fields = [
        'html5text',
        'html5date',
        'html5datetimelocal',
        'html5email',
        'html5month',
        'html5number',
        'html5range',
        'html5time',
        'html5url',
        'html5week'
    ];

    /**
     * @return $this
     */
    protected function addPrefixes()
    {
        $recaptchaPath = realpath(PIMCORE_PATH . '/../vendor/cgsmith/zf1-recaptcha-2/src');

        if ($this->getView() !== NULL) {
            $this->getView()->addHelperPath($recaptchaPath . '/Cgsmith/View/Helper', 'Cgsmith\\View\\Helper\\');
            $this->getView()->addHelperPath(PIMCORE_PLUGINS_PATH . '/Formbuilder/lib/Formbuilder/Zend/View/Helper', 'Formbuilder\\Zend\\View\\Helper\\');

            $this->getView()->headScript()->appendFile('//www.google.com/recaptcha/api.js?hl=' . $this->getView()->language);
        }

        $this->addPrefixPath(
            'Formbuilder\\Zend\\Form\\Element',
            PIMCORE_PLUGINS_PATH . '/Formbuilder/lib/Formbuilder/Zend/Form/Element/',
            \Zend_Form::ELEMENT
        );

        $this->addPrefixPath(
            'Formbuilder\\Zend\\Form\\Element\\Html5Text',
            PIMCORE_PLUGINS_PATH . '/Formbuilder/lib/Formbuilder/Zend/Form/Element/Html5Text',
            \Zend_Form::ELEMENT
        );

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
     * @param bool $forClasses
     *
     * @return array
     */
    public function getValidHtml5Elements($forClasses = FALSE)
    {
        $elements = self::$validHtml5Fields;

        if ($forClasses == TRUE) {
            $elements = array_diff($elements, ['html5range']);
        }

        return $elements;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isValidHtml5Element($type = '')
    {
        return in_array(strtolower($type), self::$validHtml5Fields);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isValidFormBuilderElement($type = '')
    {
        return in_array(strtolower($type), self::$validFields) || in_array(strtolower($type), self::$validHtml5Fields);
    }

    /**
     * Validate the form
     * This Method is a clone from the original \Zend_Form->isValid() Method. Because the google reCaptcha only allows one-time-success,
     * we need to suppress the captcha validation, until all other fields are ok.
     *
     * @param  array       $data
     * @param  null|string $suppressCaptchaValidation
     *
     * @throws \Zend_Form_Exception
     * @return bool
     */
    public function isValid($data, $suppressCaptchaValidation = NULL)
    {
        if (!is_array($data)) {
            throw new \Zend_Form_Exception(__METHOD__ . ' expects an array');
        }

        $translator = $this->getTranslator();
        $valid = TRUE;
        $eBelongTo = NULL;

        if ($this->isArray()) {
            $eBelongTo = $this->getElementsBelongTo();
            $data = $this->_dissolveArrayValue($data, $eBelongTo);
        }

        $context = $data;

        /** @var \Zend_Form_Element $element */
        foreach ($this->getElements() as $key => $element) {
            if (!is_null($suppressCaptchaValidation) && $key == $suppressCaptchaValidation) {
                continue;
            }

            if ($translator !== NULL && $this->hasTranslator() && !$element->hasTranslator()) {
                $element->setTranslator($translator);
            }

            $check = $data;

            if (($belongsTo = $element->getBelongsTo()) !== $eBelongTo) {
                $check = $this->_dissolveArrayValue($data, $belongsTo);
            }

            if (!isset($check[$key])) {
                $valid = $element->isValid(NULL, $context) && $valid;
            } else {
                $valid = $element->isValid($check[$key], $context) && $valid;
                $data = $this->_dissolveArrayUnsetKey($data, $belongsTo, $key);
            }
        }

        /** @var \Zend_Form_SubForm $form */
        foreach ($this->getSubForms() as $key => $form) {
            if ($translator !== NULL && $this->hasTranslator() && !$form->hasTranslator()) {
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
            return FALSE;
        }

        return $valid;
    }

    /**
     * If Twitter Form has been activated, we have to get rid of the input bootstrap classes.
     *
     * @param  string             $type
     * @param  string             $name
     * @param  array|\Zend_Config $options
     *
     * @return \Zend_Form_Element
     */
    public function createElement($type, $name, $options = NULL)
    {
        if (NULL !== $options && $options instanceof \Zend_Config) {
            $options = $options->toArray();
        }

        if ((NULL === $options) || !is_array($options)) {
            $options = [];
        }

        $decorators = [];

        //get default decorators, but only if we're a bootstrap form!
        if ($this->isBootstrapForm) {
            $decorators = $this->getDefaultDecoratorsByElementType($type);
        }

        if (isset($options['additionalDecorators']) && is_array($options['additionalDecorators'])) {
            $decorators = array_merge($decorators, $options['additionalDecorators']);
        }

        if (!empty($decorators)) {
            $options['decorators'] = $decorators;
        }

        //finished if we're not a bootstrap form!
        if (!$this->isBootstrapForm && !$this->isValidHtml5Element($type)) {
            return parent::createElement($type, $name, $options);
        }

        //add for attribute to bootstrap checkbox label, if element is checkbox
        if ($type === 'checkbox' && is_array($options['decorators'])) {
            foreach ($options['decorators'] as &$decorator) {
                $clKey = array_search('CheckboxLabel', $decorator);
                if ($clKey !== FALSE) {
                    $decorator[$clKey + 1] = ['for' => isset($options['id']) ? $options['id'] : $name];
                }
            }
        }

        if (in_array(strtolower($type), $this->getValidHtml5Elements(TRUE))) {
            if (NULL === $options) {
                $options = ['class' => 'form-control'];
            } else if (array_key_exists('class', $options)) {
                if (!strstr($options['class'], 'form-control')) {
                    $options['class'] .= ' form-control';
                    $options['class'] = trim($options['class']);
                }
            } else {
                $options['class'] = 'form-control';
            }
        }

        return parent::createElement($type, $name, $options);
    }

}

