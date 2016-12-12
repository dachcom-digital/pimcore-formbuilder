<?php

namespace Formbuilder\Lib\Form\Frontend;

use Pimcore\Tool;
use Formbuilder\Model\Form;
use Formbuilder\Lib\Form\Backend\Builder as BackendBuilder;

class Builder {

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
    protected $reCaptchaV2Key = NULL;

    /**
     * @var string
     */
    protected static $defaultFormClass = 'Zend_Form';

    /**
     * @var string
     */
    protected static $defaultSubFormClass = 'Zend_Form_SubForm';

    /**
     * @var string
     */
    protected $formClass = '\\Formbuilder\\Zend\\Form\\DefaultForm';

    /**
     * @var string
     */
    protected $subFormClass = '\\Formbuilder\\Zend\\Form\\DefaultSubForm';

    /**
     * @param $defaultFormClass
     */
    public static function setDefaultFormClass($defaultFormClass)
    {
        self::$defaultFormClass = $defaultFormClass;
    }

    /**
     * @param bool $subForm

     * @return string
     */
    public static function getDefaultFormClass( $subForm = FALSE )
    {
        return $subForm ? self::$defaultSubFormClass : self::$defaultFormClass;
    }

    /**
     * @param $formClass
     */
    public function setFormClass($formClass)
    {
        $this->formClass = (string)$formClass;
    }

    /**
     * @param bool $subForm
     *
     * @return string
     */
    public function getFormClass( $subForm = FALSE )
    {
        $formClass = $subForm ? 'subFormClass' : 'formClass';

        if($this->$formClass !== NULL )
        {
            return $this->$formClass;
        }
        else
        {
            return self::getDefaultFormClass($subForm);
        }
    }

    /**
     * @param      $formClass
     * @param bool $subForm
     *
     * @return null|string|\Zend_Form|\Zend_Form_SubForm
     */
    protected function getZendFormClass( $formClass, $subForm = FALSE )
    {
        $mappedClass = NULL;

        if( is_string( $formClass ) )
        {
            $formClassName = $subForm ? ucfirst( $formClass ) . 'SubForm' : ucfirst( $formClass ) . 'Form';

            if(  class_exists( '\\Formbuilder\\Zend\\Form\\' . $formClassName ) )
            {
                $mappedClass = '\\Formbuilder\\Zend\\Form\\' . $formClassName;
            }
        }
        else if( ($subForm == FALSE && $formClass instanceof \Zend_Form) || ( $subForm == TRUE && $formClass instanceof \Zend_Form_SubForm ))
        {
            $mappedClass = $formClass;
        }
        else
        {
            $mappedClass = $this->getFormClass( $subForm );
        }

        return $mappedClass;

    }

    /**
     * If $dynamic equal true, the form form is completely rebuild. It is useful if you need to interact to the form with hooks.
     *
     * @param int       $formId
     * @param string    $locale
     * @param string|\stdClass $formClass
     * @param array     $params
     *
     * @return bool|\Zend_Form
     */
    public function getForm($formId, $locale = NULL, $formClass = 'Default', $params = [])
    {
        $this->getLanguages();

        if ( !is_numeric($formId) )
        {
            return FALSE;
        }

        if ( !file_exists(FORMBUILDER_DATA_PATH . '/main_' . $formId . '.json') )
        {
            return FALSE;
        }

        $this->config = new \Zend_Config_Json(FORMBUILDER_DATA_PATH . '/main_' . $formId . '.json');
        $dataStorage = $this->config->toArray();

        $builder = new BackendBuilder();
        $builder->setDatas($dataStorage);
        $builder->setLocale($locale);

        $formData = $builder->buildDynamicForm();

        $subForms = isset( $formData['subForms'] ) && !empty( $formData['subForms'] ) ? $formData['subForms'] : FALSE;

        if( $subForms !== FALSE)
        {
            $formData['subForms'] = $this->parseSubForms( $subForms, $formClass, $formId, $locale );
        }

        $formData = $this->parseFormData( $formData, $formClass );

        $mappedClass = $this->getZendFormClass( $formClass );

        return $this->instantiateForm( $formData, $mappedClass, $formId, $locale );

    }

    private function parseSubForms( $subForms, $formClass, $formId, $locale )
    {
        $forms = [];

        foreach( $subForms as $formName => $subForm )
        {
            $subFormData = $subForm[0];
            $subFormName = $subForm[1];

            $subForms = isset( $subFormData['subForms'] ) && !empty( $subFormData['subForms'] ) ? $subFormData['subForms'] : FALSE;

            if( $subForms !== FALSE )
            {
                $subFormData['subForms'] = $this->parseSubForms( $subFormData['subForms'], $formClass, $formId, $locale );
            }

            $formData = $this->parseFormData( $subFormData, $formClass );

            $mappedClass = $this->getZendFormClass( $formClass, TRUE );

            $form = $this->instantiateForm( $formData, $mappedClass, $formId, $locale );

            if( isset( $subFormData['options']['order'] ))
            {
                $form->setOrder( $subFormData['options']['order'] );
            }

            $forms[] = $form;

        }

        return $forms;

    }

    /**
     *
     * @param array $params
     * @param \Zend_Form $form
     *
     * @return array
     */
    public function parseFormParams( $params = [], $form )
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
                $this->reCaptchaV2Key = $element->getName();
                $params[ $this->reCaptchaV2Key ] = $params['g-recaptcha-response'];
                unset( $params['g-recaptcha-response'] );
                break;
            }
        }

        return $params;
    }

    /**
     * @return bool
     */
    public function hasRecaptchaV2()
    {
        return !is_null( $this->reCaptchaV2Key );
    }

    /**
     * @return null
     */
    public function getRecaptchaV2Key()
    {
        return $this->reCaptchaV2Key;
    }

    /**
     * @param  \Zend_Form $form
     * @param array $attributes
     *
     * @return \Zend_Form
     */
    public function addDefaultValuesToForm( $form, $attributes = [] )
    {
        $defaults = [
            'formData'              => NULL,
            'formPreset'            => NULL,
            'formId'                => NULL,
            'locale'                => 'en',
            'mailTemplateId'        => NULL,
            'copyMailTemplateId'    => NULL,
            'sendCopy'              => FALSE,
            'ajaxForm'              => FALSE
        ];

        $params = array_merge($defaults, $attributes);

        $form->addElement(
            'text',
            'honeypot',
            [
                'label'         => '',
                'required'      => FALSE,
                'ignore'        => TRUE,
                'class'         => 'hon-hide',
                'decorators'    => ['ViewHelper'],
                'validators'    => [
                    [
                        'validator' => 'Honeypot'
                    ]
                ]
            ]
        );

        $formData = [
            'formId'                => $params['formData']->getId(),
            'formPreset'            => $params['formPreset'],
            'language'              => $params['locale'],
            'mailTemplateId'        => $params['mailTemplateId'],
            'copyMailTemplateId'    => $params['copyMailTemplateId'],
            'sendCopy'              => $params['sendCopy']
        ];

        $form->addElement(
            'hidden',
            '_formConfig',
            [
                'ignore' => TRUE,
                'value' => htmlentities( json_encode( $formData ) )
            ]
        );

        $configData = $this->config->toArray();

        $setFormClasses = explode(' ', $form->getAttrib('class') );
        $setFormClasses[] = 'formbuilder';

        if( isset( $configData['useAjax']) && $configData['useAjax'] == TRUE )
        {
            $setFormClasses[] = 'ajax-form';
        }

        $form->setAttrib('class', implode(' ', $setFormClasses ) );

        $cmdEv = \Pimcore::getEventManager()->trigger(
            'formbuilder.form.preCreateForm',
            NULL,
            [
                'form'          => $form,
                'formPreset'    => $params['formPreset'],
                'formId'        => $params['formData']->getId(),
                'formName'      => $params['formData']->getName()
            ]
        );

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

    /**
     * @param $id
     * @param $locale
     *
     * @return \Zend_Translate_Adapter_Csv
     * @throws \Zend_Translate_Exception
     */
    protected function translateForm( $id, $locale)
    {
        $trans = new \Zend_Translate_Adapter_Csv( ['delimiter' => ',', 'disableNotices' => TRUE ] );
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
            $arrTrans = new \Zend_Translate_Adapter_Array( [ 'disableNotices' => TRUE ] );
            $arrTrans->addTranslation( [ 'content' => $file, 'locale' => $locale ] );
            $trans->addTranslation($arrTrans);
        }

        return $trans;
    }

    /**
     * @param        $form
     * @param string $formType
     *
     * @return mixed
     */
    protected function parseFormData( $form, $formType = 'Default' )
    {
        foreach( $form['elements'] as $elementName => &$element )
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

            if( file_exists( FORMBUILDER_PATH . '/lib/Formbuilder/Lib/Form/Frontend/Mapper/' . ucfirst( $element['type'] ) . '.php' ) )
            {
                /** @var Mapper\MapAbstract $className */
                $className = '\\Formbuilder\\Lib\\Form\\Frontend\\Mapper\\' . ucfirst( $element['type'] );
                $element = $className::parse( $element, $formType);
            }

        }

        return $form;
    }

    /**
     * @return array|null
     */
    protected function getLanguages()
    {
        if ($this->languages == NULL)
        {
            $languages = Tool::getValidLanguages();
            $this->languages = $languages;
        }

        return $this->languages;
    }


    /**
     * @param \Zend_Form $form
     * @param            $id
     * @param null       $locale
     *
     * @throws \Zend_Form_Exception
     * @throws \Zend_Locale_Exception
     */
    protected function initTranslation(\Zend_Form $form, $id, $locale = NULL)
    {
        if($locale === NULL)
        {
            $locale = \Zend_Locale::findLocale();
        }

        $trans = $this->translateForm($id, $locale);
        \Zend_Form::setDefaultTranslator($trans);

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

    protected function instantiateForm( $formData = [], $mappedClass, $formId, $locale)
    {
        $form = $this->createInstance($formData, $mappedClass);
        $this->initTranslation($form, $formId, $locale);

        return $this->onAfterFormInstance( $form );

    }

    /**
     * @param        $config
     * @param string $className
     *
     * @return \Zend_Form
     * @throws \Exception
     */
    protected function createInstance($config, $className = 'DefaultForm')
    {
        $reflectionClass = new \ReflectionClass($className);

        if( !($reflectionClass->isSubclassOf('Zend_Form') || $reflectionClass->name == 'Zend_Form') )
        {
            throw new \Exception('Form class must be a subclass of "Zend_Form"');
        }

        return $reflectionClass->newInstance($config);
    }

    /**
     * @param \Zend_Form $form
     *
     * @return mixed
     */
    protected function onAfterFormInstance( $form )
    {
        /** @var \Zend_Form_Element $element */
        foreach( $form->getElements() as $element)
        {
            /**
             *
             *  @fixme: Maybe it's possible to extend the Label Decorator?
             *  Now transform Label Placeholder. Because the may get translated, we need to do this here.
             *
             **/
            $label = $element->getLabel();
            if( !empty( $label ) )
            {
                $element->setLabel( \Formbuilder\Tool\Placeholder::parse( $label ) );
            }
        }

        return $form;
    }
}