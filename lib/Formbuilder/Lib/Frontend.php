<?php

namespace Formbuilder\Lib;

use Pimcore\Tool;
use Formbuilder\Model\Form;
use Formbuilder\Lib\Builder;

use Formbuilder\Zend\TwitterHorizontalForm;
use Formbuilder\Zend\TwitterVerticalForm;

class Frontend {

    protected $languages = NULL;

    protected $config = NULL;

    protected $recaptchaV2Key = NULL;

    protected static $defaultFormClass = 'Zend_Form';

    protected $formClass = 'Formbuilder\Zend\DefaultForm';

    public static function setDefaultFormClass($defaultFormClass)
    {
        self::$defaultFormClass = $defaultFormClass;
    }

    public static function getDefaultFormClass()
    {
        return self::$defaultFormClass;
    }

    public function setFormClass($formClass)
    {
        $this->formClass = (string)$formClass;
    }

    public function getFormClass()
    {
        if(null !== $this->formClass)
        {
            return $this->formClass;
        }
        else
        {
            return self::getDefaultFormClass();
        }
    }

    protected function getLanguages()
    {
        if ($this->languages == null)
        {
            $languages = Tool::getValidLanguages();
            $this->languages = $languages;
        }

        return $this->languages;

    }

    protected function getStaticForm($id, $locale, $className = 'DefaultForm')
    {
        if (file_exists(FORMBUILDER_DATA_PATH . "/form/form_" . $id . ".ini"))
        {
            $this->config = new \Zend_Config_Ini(FORMBUILDER_DATA_PATH . "/form/form_" . $id . ".ini", 'config');

            $formData = $this->parseFormData( $this->config->form->toArray() );

            $form = $this->createInstance($formData, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        }
        else
        {
            return false;
        }
    }

    protected function getDynamicForm($id, $locale, $className = 'DefaultForm')
    {
        if (file_exists(FORMBUILDER_DATA_PATH . "/main_" . $id . ".json"))
        {
            $this->config = new \Zend_Config_Json(FORMBUILDER_DATA_PATH . "/main_" . $id . ".json");
            $datas = $this->config->toArray();

            $builder = new Builder();
            $builder->setDatas($datas);
            $builder->setLocale($locale);
            $array = $builder->buildDynamicForm();

            $formData = $this->parseFormData( $array );

            $form = $this->createInstance($formData, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        }
        else
        {
            return false;
        }
    }

    protected function createInstance($config, $className = 'DefaultForm')
    {
        $reflClass = new \ReflectionClass($className);

        if(!($reflClass->isSubclassOf('Zend_Form') || $reflClass->name == 'Zend_Form'))
        {
            throw new \Exception('Form class must be a subclass of "Zend_Form"');
        }

        return $reflClass->newInstance($config);
    }

    protected function initTranslation(\Zend_Form $form, $id, $locale = null)
    {
        if($locale === null)
        {
            $locale = \Zend_Locale::findLocale();
        }

        $trans = $this->translateForm($id, $locale);

        if ($locale != null && $locale != "")
        {
            if(null === $form->getTranslator())
            {
                $form->setTranslator($trans);
            }
            else
            {
                $form->getTranslator()->addTranslation($trans);
            }
        }
    }

    public function getTwitterForm($formId, $locale = null,$horizontal=true)
    {
        $this->getLanguages();

        if (is_numeric($formId) == true)
        {
            if (file_exists(FORMBUILDER_DATA_PATH . "/form/form_" . $formId . ".ini"))
            {
                $this->config = new \Zend_Config_Ini(FORMBUILDER_DATA_PATH . "/form/form_" . $formId . ".ini", 'config');

                $trans = $this->translateForm($formId, $locale);

                \Zend_Form::setDefaultTranslator($trans);

                $formData = $this->parseFormData( $this->config->form->toArray() );

                if($horizontal==true)
                {
                    $form = new TwitterHorizontalForm($formData);
                }
                else
                {
                    $form = new TwitterVerticalForm($formData);
                }

                $form->setDisableTranslator(true);

                if ($locale != null && $locale != "")
                {
                    $form->setTranslator($trans);
                }

                return $form;

            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * If $dynamic equal true, the form form is completely rebuild. It is useful if you need to interact to the form with hooks.
     *
     * @param int $formId
     * @param string $locale
     * @param boolean $dynamic
     * @param string Custom form class
     * @return \Formbuilder\Zend\DefaultForm
     */
    public function getForm($formId, $locale = null, $dynamic = false, $formClass = null)
    {
        $this->getLanguages();

        if (is_numeric($formId) == true)
        {
            $class = $formClass ?: $this->getFormClass();
            if ($dynamic == false)
            {
                $form = $this->getStaticForm($formId, $locale, $class);
            }
            else
            {
                $form = $this->getDynamicForm($formId, $locale, $class);
            }

            //correctly set recaptcha to https if request is over https
            if(\Zend_Controller_Front::getInstance()->getRequest()->isSecure())
            {
                //@fixme: deprecated?
            }

            return $form;
        }
        else
        {
            return false;
        }
    }

    public function parseFormParams( $params = array(), $form )
    {
        //no Recaptcha (v2) requested!
        if( !isset( $params['g-recaptcha-response'] ) )
        {
            return $params;
        }

        foreach ($form->getElements() as $key => $element)
        {
            if($element instanceof \Cgsmith\Form\Element\Recaptcha )
            {
                $element->setIgnore(TRUE);
                $this->recaptchaV2Key = $element->getName();
                $params[ $this->recaptchaV2Key ] = $params['g-recaptcha-response'];
                unset( $params['g-recaptcha-response'] );
                break;
            }
        }

        return $params;
    }

    public function hasRecaptchaV2()
    {
        return !is_null( $this->recaptchaV2Key );
    }

    public function getRecaptchaV2Key()
    {
        return $this->recaptchaV2Key;
    }

    public function addDefaultValuesToForm( $form, $attributes = array() )
    {
        $defaults = array(
            'formId' => NULL,
            'locale' => 'en',
            'mailTemplate' => NULL,
            'ajaxForm' => FALSE
        );

        $params = array_merge($defaults, $attributes);

        $form->addElement(
            'text',
            'honeypot',
            array(
                'label' => '',
                'required' => false,
                'ignore' => TRUE,
                'class' => 'hon-hide',
                'decorators' => array('ViewHelper'),
                'validators' => array(
                    array(
                        'validator' => 'Honeypot'
                    )
                )
            )
        );

        $form->addElement(
            'hidden',
            '_formId',
            array(
                'ignore' => TRUE,
                'value' => $params['formId']
            )
        );

        $form->addElement(
            'hidden',
            '_language',
            array(
                'ignore' => TRUE,
                'value' => $params['locale']
            )
        );

        if( $params['mailTemplate'] instanceof \Pimcore\Model\Document\Email ) {

            $form->addElement(
                'hidden',
                '_mailTemplate',
                array(
                    'ignore' => TRUE,
                    'value' => $params['mailTemplate']->getId()
                )
            );

        }

        $configData = $this->config->toArray();

        $setFormClasses = explode(' ', $form->getAttrib('class') );
        $setFormClasses[] = 'formbuilder';

        if( isset( $configData['form']['useAjax']) && $configData['form']['useAjax'] == TRUE )
        {
            $setFormClasses[] = 'ajax-form';
        }

        $form->setAttrib('class', implode(' ', $setFormClasses ) );

        return $form;

    }

    protected function translateForm( $id, $locale)
    {
        $trans = new \Zend_Translate_Adapter_Csv(array("delimiter" => ",", "disableNotices" => true));
        $file = FORMBUILDER_DATA_PATH . "/lang/form_" . $id . "_" . $locale . ".csv";

        if (file_exists($file))
        {
            $trans->addTranslation(
                array(
                    'content' => $file,
                    'locale' => $locale
                ));
        }

        $file = FORMBUILDER_DEFAULT_ERROR_PATH . "/" . $locale . "/Zend_Validate.php";

        if (file_exists($file))
        {
            $arrTrans = new \Zend_Translate_Adapter_Array( array("disableNotices" => true)) ;
            $arrTrans->addTranslation(array( "content" => $file, "locale" => $locale));
            $trans->addTranslation($arrTrans);
        }

        return $trans;

    }

    protected function parseFormData( $form )
    {
        foreach( $form['elements'] as $elementName => &$element) {

            if( !is_array( $element ) )
            {
                continue;
            }

            //set class to each field to allow ajax validation!
            $classes = '';
            if( isset( $element['options']['class'] ) )
            {
                $classes = $element['options']['class'];
            }

            $element['options']['class'] = $classes . ' element-' . $elementName;

            //rearrange reCaptcha (v2) config
            if( $element['type'] == 'captcha' && $element['options']['captcha'] == 'reCaptcha' && isset( $element['options']['captchaOptions'] ) )
            {
                $captchaOptions = $element['options']['captchaOptions'];

                $element['type'] = 'reCaptcha';
                $element['options'] = array(
                    'secretKey' => $captchaOptions['secretKey'],
                    'siteKey' => $captchaOptions['siteKey'],
                    'classes' => array($element['options']['class'])
                );

                unset( $element['options']['captchaOptions']);

            }

            //allow "please select" field in multi select element
            if( $element['type'] == 'select' && isset( $element['options']['multiOptions']))
            {
                $realOptions = array();
                foreach( $element['options']['multiOptions'] as $optionKey => $optionValue)
                {
                    if( $optionKey == 'choose')
                    {
                        $optionKey = '';
                    }

                    $realOptions[$optionKey] = $optionValue;

                }

                $element['options']['multiOptions'] = $realOptions;
            }

        }

        return $form;
    }
}