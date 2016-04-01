<?php

namespace Formbuilder\Lib;

use Pimcore\Tool;
use Formbuilder\Model\Form;
use Formbuilder\Lib\Builder;

class Frontend {

    protected $languages = null;

    protected $config = null;

    protected static $defaultFormClass = 'Zend_Form';

    protected $formClass = 'Zend_Form';

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

    protected function getStaticForm($id, $locale, $className = 'Zend_Form')
    {
        if (file_exists(FORMBUILDER_DATA_PATH . "/form/form_" . $id . ".ini"))
        {
            $this->config = new \Zend_Config_Ini(FORMBUILDER_DATA_PATH . "/form/form_" . $id . ".ini", 'config');

            $form = $this->createInstance($this->config->form, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        }
        else
        {
            return false;
        }
    }

    protected function getDynamicForm($id, $locale, $className = 'Zend_Form')
    {
        if (file_exists(FORMBUILDER_DATA_PATH . "/main_" . $id . ".json"))
        {
            $this->config = new \Zend_Config_Json(FORMBUILDER_DATA_PATH . "/main_" . $id . ".json");
            $datas = $this->config->toArray();

            $builder = new Builder();
            $builder->setDatas($datas);
            $builder->setLocale($locale);
            $array = $builder->buildDynamicForm();

            $form = $this->createInstance($array, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        }
        else
        {
            return false;
        }
    }

    protected function createInstance($config, $className = 'Zend_Form')
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

                if($horizontal==true)
                {
                    $form = new \Twitter_Bootstrap3_Form_Horizontal($this->config->form);
                }
                else
                {
                    $form = new \Twitter_Bootstrap3_Form_Vertical($this->config->form);
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
     * @return \Zend_Form
     */
    public function getForm($formId, $locale=null, $dynamic=false, $formClass = null)
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
                /**@var \Zend_Form $form */
                $elements = $form->getElements();

                foreach($elements as $element)
                {
                    if(get_class($element) == 'Zend_Form_Element_Captcha' )
                    {
                        /**@var  \Zend_Form_Element_Captcha $element */
                        $cap = $element->getCaptcha();
                        $cap->getService()->setParams(array('ssl'=>true));
                    }
                }
            }

            return $form;
        }
        else
        {
            return false;
        }
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

        $form->addElementPrefixPath(
            'Formbuilder',
            'Formbuilder/Zend/Form/'
        );

        $form->addElement(
            'text',
            'honeypot',
            array(
                'label' => '',
                'required' => false,
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
                'value' => $params['formId']
            )
        );

        $form->addElement(
            'hidden',
            '_language',
            array(
                'value' => $params['locale']
            )
        );

        if( $params['mailTemplate'] instanceof \Pimcore\Model\Document\Email ) {

            $form->addElement(
                'hidden',
                '_mailTemplate',
                array(
                    'value' => $params['mailTemplate']->getId()
                )
            );

        }

        $configData = $this->config->toArray();

        $settedFormClasses = explode(' ', $form->getAttrib('class') );

        $settedFormClasses[] = 'formbuilder';

        if( isset( $configData['form']['useAjax']) && $configData['form']['useAjax'] == TRUE )
        {
            $settedFormClasses[] = 'ajax-form';
        }

        $form->setAttrib('class', implode(' ', $settedFormClasses ) );

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

}