<?php

namespace Formbuilder\Lib;

use Pimcore\Tool;
use Formbuilder\Model\Form;

use Formbuilder\Zend\TwitterHorizontalForm;
use Formbuilder\Zend\TwitterVerticalForm;

class Frontend {

    /**
     * @var null
     */
    protected $languages = NULL;

    /**
     * @var null
     */
    protected $config = NULL;

    /**
     * @var null
     */
    protected $recaptchaV2Key = NULL;

    /**
     * @var string
     */
    protected static $defaultFormClass = 'Zend_Form';

    /**
     * @var string
     */
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
        if (file_exists(FORMBUILDER_DATA_PATH . '/form/form_' . $id . '.ini'))
        {
            $this->config = new \Zend_Config_Ini(FORMBUILDER_DATA_PATH . '/form/form_' . $id . '.ini', 'config');

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
        if (file_exists(FORMBUILDER_DATA_PATH . '/main_' . $id . '.json'))
        {
            $this->config = new \Zend_Config_Json(FORMBUILDER_DATA_PATH . '/main_' . $id . '.json');
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

        if( !($reflClass->isSubclassOf('Zend_Form') || $reflClass->name == 'Zend_Form') )
        {
            throw new \Exception('Form class must be a subclass of "Zend_Form"');
        }

        return $reflClass->newInstance($config);
    }

    protected function initTranslation(\Zend_Form $form, $id, $locale = NULL)
    {
        if($locale === NULL)
        {
            $locale = \Zend_Locale::findLocale();
        }

        $trans = $this->translateForm($id, $locale);

        if ($locale != NULL && $locale != '')
        {
            if($form->getTranslator() === NULL)
            {
                $form->setTranslator($trans);
            }
            else
            {
                $form->getTranslator()->addTranslation($trans);
            }
        }
    }

    public function getTwitterForm($formId, $locale = NULL, $horizontal = TRUE)
    {
        $this->getLanguages();

        if (is_numeric($formId) == TRUE)
        {
            if (file_exists(FORMBUILDER_DATA_PATH . '/form/form_' . $formId . '.ini'))
            {
                $this->config = new \Zend_Config_Ini(FORMBUILDER_DATA_PATH . '/form/form_' . $formId . '.ini', 'config');

                $trans = $this->translateForm($formId, $locale);

                \Zend_Form::setDefaultTranslator($trans);

                $formData = $this->parseFormData( $this->config->form->toArray() );

                if($horizontal == TRUE)
                {
                    $form = new TwitterHorizontalForm($formData);
                }
                else
                {
                    $form = new TwitterVerticalForm($formData);
                }

                $form->setDisableTranslator(TRUE);

                if ($locale != NULL && $locale != '')
                {
                    $form->setTranslator($trans);
                }

                return $form;

            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
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
    public function getForm($formId, $locale = NULL, $dynamic = FALSE, $formClass = NULL)
    {
        $this->getLanguages();

        if (is_numeric($formId) == TRUE)
        {
            $class = $formClass ?: $this->getFormClass();
            if ($dynamic == FALSE)
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
            return FALSE;
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
            'formName' => NULL,
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

        if( $params['mailTemplate'] instanceof \Pimcore\Model\Document\Email )
        {
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

        $cmdEv = \Pimcore::getEventManager()->trigger('formbuilder.form.preCreateForm', NULL, ['form' => $form, 'formId' => $params['formId'], 'formName' => $params['formName']]);

        if ($cmdEv->stopped())
        {
            $customForm = $cmdEv->last();

            if( $customForm instanceof \Zend_Form )
            {
                $form = $customForm;
            }

        }

        return $form;

    }

    protected function translateForm( $id, $locale)
    {
        $trans = new \Zend_Translate_Adapter_Csv(array('delimiter' => ',', 'disableNotices' => true));
        $file = FORMBUILDER_DATA_PATH . '/lang/form_' . $id . '_' . $locale . '.csv';

        if (file_exists($file))
        {
            $trans->addTranslation(
                array(
                    'content' => $file,
                    'locale' => $locale
                )
            );
        }

        $file = FORMBUILDER_DEFAULT_ERROR_PATH . '/' . $locale . '/Zend_Validate.php';

        if ( file_exists($file) )
        {
            $arrTrans = new \Zend_Translate_Adapter_Array( array('disableNotices' => TRUE));
            $arrTrans->addTranslation(array( 'content' => $file, 'locale' => $locale));
            $trans->addTranslation($arrTrans);
        }

        return $trans;

    }

    protected function parseFormData( $form )
    {
        foreach( $form['elements'] as $elementName => &$element)
        {
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
            if( $element['type'] === 'captcha' && $element['options']['captcha'] == 'reCaptcha' && isset( $element['options']['captchaOptions'] ) )
            {
                $captchaOptions = $element['options']['captchaOptions'];

                $element['type'] = 'recaptcha';
                $element['options'] = array(
                    'secretKey' => $captchaOptions['secretKey'],
                    'siteKey' => $captchaOptions['siteKey'],
                    'classes' => array($element['options']['class'])
                );

                unset( $element['options']['captchaOptions']);

            }
            //check if image is src
            else if( $element['type'] === 'image')
            {
                if( !isset( $element['options']['useAsInputField'] ) || (int) $element['options']['useAsInputField'] !== 1)
                {
                    $element['type'] = 'imageTag';
                }

            }
            //set right upload options
            else if( $element['type'] === 'file')
            {
                $element['options']['destination'] = PIMCORE_WEBSITE_PATH . '/' . ltrim($element['options']['destination'] , '/');

                //if it's a multifile, use a javascript library!
                if( (int) $element['options']['multiFile'] === 1 )
                {
                    $element['type'] = 'html5File';

                    if( !isset( $element['options']['validators'] ) )
                    {
                        $element['options']['validators'] = array();
                    }

                    $element['options']['validators']['html5file'] = array(
                        'validator' => 'Html5File',
                        'options' => array()
                    );

                }

            }
            //allow "please select" field in multi select element
            else if( $element['type'] === 'select' && isset( $element['options']['multiOptions'] ))
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