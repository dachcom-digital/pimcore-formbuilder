<?php

namespace Formbuilder\Lib;

use Pimcore\Tool;
use Formbuilder\Model\Form;
use Formbuilder\Lib\Builder;

class Frontend {

    protected $languages = null;

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

        if (file_exists(PIMCORE_PLUGINS_PATH . "/Formbuilder/data/form/form_" . $id . ".ini"))
        {
            $config = new \Zend_Config_Ini(PIMCORE_PLUGINS_PATH . "/Formbuilder/data/form/form_" . $id . ".ini", 'config');

            $form = $this->createInstance($config->form, $className);
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
        if (file_exists(PIMCORE_PLUGINS_PATH . "/Formbuilder/data/main_" . $id . ".json"))
        {
            $config = new \Zend_Config_Json(PIMCORE_PLUGINS_PATH . "/Formbuilder/data/main_" . $id . ".json");
            $datas = $config->toArray();

            $builder = new Builder();
            $builder->setDatas($datas);
            $builder->setLocale($locale);
            $array = $builder->buildDynamicForm();

            $form = $this->createInstance($array, $className);
            $this->initTranslation($form, $id, $locale);

            return $form;
        } else {
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
            } else {
                $form->getTranslator()->addTranslation($trans);
            }
        }
    }

    public function getTwitterForm($name, $locale = null,$horizontal=true)
    {
        $this->getLanguages();

        $table = new Builder();
        $id = $table->getIdByName($name);

        if (is_numeric($id) == true)
        {
            if (file_exists(PIMCORE_PLUGINS_PATH . "/Formbuilder/data/form/form_" . $id . ".ini"))
            {
                $config = new \Zend_Config_Ini(PIMCORE_PLUGINS_PATH . "/Formbuilder/data/form/form_" . $id . ".ini", 'config');

                $trans = $this->translateForm($id, $locale);

                \Zend_Form::setDefaultTranslator($trans);

                if($horizontal==true)
                {
                    $form = new Twitter_Bootstrap_Form_Horizontal($config->form);
                }
                else
                {
                    $form = new Twitter_Bootstrap_Form_Vertical($config->form);
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
     * If $dynamic equal true, the form form is completly rebuild. It is useful if you need to interact to the form with hooks.
     *
     * @param string $name
     * @param string $locale
     * @param boolean $dynamic
     * @param string Custom form class
     * @return Zend_Form
     */
    public function getForm($name, $locale=null, $dynamic=false, $formClass = null)
    {
        $this->getLanguages();

        $form = new Form();
        $id = $form->getIdByName($name);

        if (is_numeric($id) == true)
        {
            $class = $formClass ?: $this->getFormClass();
            if ($dynamic == false)
            {
                $form = $this->getStaticForm($id, $locale, $class);
            }
            else
            {
                $form = $this->getDynamicForm($id, $locale, $class);
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
        } else {
            return false;
        }
    }

    protected function translateForm( $id, $locale)
    {
        $trans = new \Zend_Translate_Adapter_Csv(array("delimiter" => ",", "disableNotices" => true));
        $file = PIMCORE_PLUGINS_PATH . "/Formbuilder/data/lang/form_" . $id . "_" . $locale . ".csv";

        if (file_exists($file))
        {
            $trans->addTranslation(
                array(
                    'content' => $file,
                    'locale' => $locale
                ));
        }

        $file = PIMCORE_PLUGINS_PATH . "/Formbuilder/data/lang/errors/" . $locale . "/Zend_Validate.php";

        if (file_exists($file))
        {
            $arrTrans = new \Zend_Translate_Adapter_Array(array("disableNotices" => true));
            $arrTrans->addTranslation(array("content" => $file, "locale" => $locale));
            $trans->addTranslation($arrTrans);
        }

        return $trans;

    }

}