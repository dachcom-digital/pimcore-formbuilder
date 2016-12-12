<?php

namespace Formbuilder\Lib\Form\Backend;

use Pimcore\Tool;

class Builder {

    /**
     * @var null
     */
    public $datas = null;

    /**
     * @var null
     */
    public $config = null;

    /**
     * @var null
     */
    public $id = null;

    /**
     * @var null
     */
    protected $translate = null;

    /**
     * @var null
     */
    protected $translateValidator = null;

    /**
     * @var null
     */
    public $translations = null;

    /**
     * @var null
     */
    public $languages = null;

    /**
     * @var null
     */
    public $locale = null;

    /**
     * @var int
     */
    public $subFormCounter = 1;

    /**
     * @var array
     */
    public $disallowedFromOptionFields = ['label', 'description', 'order', 'template', 'name', 'fieldtype'];

    /**
     * @var array
     */
    public $mergeOptionFields = ['attrib'];

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function setDatas($datas)
    {
        if (!is_array($datas))
        {
            return false;
        }
        $this->datas = $datas;
        return $this;
    }

    public function getDatas()
    {
        return $this->datas;
    }

    public function getLanguages()
    {
        if ($this->languages == null)
        {
            $languages = Tool::getValidLanguages();
            $this->languages = $languages;
        }

        return $this->languages;
    }

    public function buildDynamicForm()
    {
        $this->createForm();
        $this->config = $this->correctArray($this->config);

        return $this->config['config']['form'];
    }

    public function buildForm($id)
    {
        $this->id = $id;

        $this->getLanguages();

        $this->createForm();

        $this->buildTranslate();

        return true;
    }

    protected function createForm()
    {
        $this->translate = [];
        $this->translateValidator = [];

        if ( !is_array($this->datas) )
        {
            return false;
        }

        $this->config = [];

        if( isset($this->datas['mainDefinitions']['childs']) )
        {
            $this->config['config']['form'] = $this->parseFormChilds( $this->datas['mainDefinitions']['childs'] );
        }

        if( !isset( $this->config['config']['form']['elements'] ) ) {
            $this->config['config']['form']['elements'] = [];
        }

        $this->config['config']['form']['action'] = $this->datas['action'];
        $this->config['config']['form']['method'] = $this->datas['method'];
        $this->config['config']['form']['enctype'] = $this->datas['enctype'];
        $this->config['config']['form']['useAjax'] = $this->datas['useAjax'];

        if( $this->datas['noValidate'] !== TRUE)
        {
            $this->config['config']['form']['novalidate'] = 1;
        }

        $multi = $this->buildMultiData( $this->datas['attrib'] );

        if ( count($multi) > 0 )
        {
            $this->config['config']['form'] = array_merge($this->config['config']['form'], $multi);
        }

    }

    protected function parseFormChilds( $childElements )
    {
        $formData = $this->_setElement( $childElements, [] );
        return $formData;
    }

    protected function _setElement( $elements, $formData, $optionalParams = [] )
    {
        $parent = isset( $optionalParams['parent'] ) ? $optionalParams['parent'] : NULL;

        foreach( $elements as $data )
        {
            switch( $data['fieldtype'] )
            {
                case 'container':

                    //DisplayGroups in Container not Allowed! :(
                    if( $parent === 'displayGroup')
                    {
                        continue;
                    }

                    if( !isset( $formData['subForms'] ) )
                    {
                        $formData['subForms'] = [];
                    }

                    $formData['subForms'] = array_merge(
                        $formData['subForms'],
                        [
                            $data['name'] => [ $this->_buildSubForm($data, $optionalParams), $data['name'] ]
                        ]
                    );

                    break;

                case 'displayGroup':

                    if( !isset( $formData['displayGroups'] ) )
                    {
                        $formData['displayGroups'] = [];
                    }

                    if( !isset( $formData['elements'] ) )
                    {
                        $formData['elements'] = [];
                    }

                    $displayGroup = $this->_buildDisplayGroup($data, $optionalParams);
                    $displayGroupElement = [];

                    if( isset( $displayGroup['elements'] ) )
                    {
                        $formData['elements'] = array_merge( $formData['elements'], $displayGroup['elements'] );

                        foreach( $displayGroup['elements'] as $elementName => $elementData )
                        {
                            $displayGroupElement[ $data['name'] ]['elements'][$elementName] = $elementName;
                        }
                    }

                    if( isset( $displayGroup['options'] ) )
                    {
                        $displayGroupElement[ $data['name'] ]['options'] = $displayGroup['options'];
                    }

                    $formData['displayGroups'] = array_merge(
                        $formData['displayGroups'],
                        $displayGroupElement
                    );

                    break;

                default:

                    if( !isset( $formData['elements'] ) )
                    {
                        $formData['elements'] = [];
                    }

                    $formData['elements'] = array_merge(
                        $formData['elements'],
                        $this->_buildField($data, $optionalParams)
                    );

            }
        }

        return $formData;
    }

    protected function _buildSubForm( $elementData, $optionalParams = [] )
    {
        //Set Translation
        $this->translate[ $elementData['name'] ] = $elementData['translate'];
        unset( $elementData['translate'] );

        $options = [];
        $subForm = [];

        foreach ($elementData as $key => $data)
        {
            $dataType = gettype($data);

            switch ($dataType)
            {
                case 'boolean':

                    $options[$key] = (bool) $data;
                    break;

                case 'array':

                    if ($key === 'childs')
                    {
                        $optionalParams['parent'] = 'subForm';
                        $optionalParams['template'] = isset( $elementData['template'] ) ? $elementData['template'] : NULL;
                        $subForm = $this->_setElement( $data, $subForm, $optionalParams );
                    }
                    else
                    {
                        $options = $this->addFieldOptions($data, $options, $key);
                    }

                    break;

                default:

                    $options = $this->addFieldOptions($data, $options, $key);
                    break;
            }

        }

        $subForm['options'] = $options;

        $attributes = [];

        foreach( $options as $optionName => $optionValue )
        {
            $attributes[ $optionName ] = $optionValue;
        }

        $className = 'sub-form-wrapper ' . $elementData['name'];

        if( isset( $attributes[ 'class' ] ) )
        {
            $attributes['class'] .= ' ' . $className;
        }
        else
        {
            $attributes['class'] = $className;
        }

        if( !isset( $subForm['elements'] ) )
        {
            $subForm['elements'] = [];
        }

        $subForm['decorators'] = [
            'FormElements',
            [
                ['formBuilderGroupWrapper' => 'HtmlTag'],
                array_merge( ['tag' => 'div'], $attributes )
            ]
        ];

        $subForm['order'] = $this->subFormCounter;

        if( !empty( $elementData['template'] ) )
        {
            $configTemp = \Formbuilder\Model\Configuration::get('form.area.groupTemplates');

            if( !empty( $configTemp ) && isset( $configTemp[ $elementData['template'] ]['group']['decorators'] ))
            {
                $subForm['decorators'] = array_merge( $subForm['decorators'], $configTemp[ $elementData['template'] ]['group']['decorators']);
            }
        }

        $this->subFormCounter++;

        return $subForm;
    }

    protected function _buildDisplayGroup( $elementData, $optionalParams = [] )
    {
        //Set Translation
        $this->translate[ $elementData['name'] ] = $elementData['translate'];
        unset( $elementData['translate'] );

        $options = [];
        $displayGroup = [];

        foreach ($elementData as $key => $data)
        {
            $dataType = gettype($data);

            switch ($dataType)
            {
                case 'boolean':

                    $options[$key] = (bool) $data;
                    break;

                case 'array':

                    if ( $key === 'childs' )
                    {
                        $optionalParams['parent'] = 'displayGroup';
                        $displayGroup = $this->_setElement( $data, $displayGroup, $optionalParams );
                    }
                    else
                    {
                        $options = $this->addFieldOptions($data, $options, $key);
                    }

                    break;

                default:

                    $options = $this->addFieldOptions($data, $options, $key);
                    break;
            }

        }


        $options['decorators'] = [
            'FormElements',
            'Fieldset'
        ];

        //if there has been set a template, apply it to the displayGroup => all templates in nested elements will be skipped!
        if( isset( $optionalParams['template'] ) && !empty( $optionalParams['template']) )
        {
            $configTemp = \Formbuilder\Model\Configuration::get('form.area.groupTemplates');

            if( !empty( $configTemp ) && isset( $configTemp[ $optionalParams['template'] ]['elements']['decorators'] ))
            {
                $options['decorators'] = array_merge( $options['decorators'], $configTemp[ $optionalParams['template'] ]['elements']['decorators']);
            }

        }

        $displayGroup['options'] = $options;
        $displayGroup['order'] = $this->subFormCounter;

        $this->subFormCounter++;

        return $displayGroup;
    }

    protected function _buildField( $elementData, $optionalParams = [] )
    {
        //Set Translation
        $this->translate[ $elementData['name'] ] = $elementData['translate'];

        $config = [];
        $options = [];

        $config[ $elementData['name'] ] = [];
        $config[ $elementData['name'] ][ 'type' ] = $elementData['fieldtype'];

        $cClass = $elementData['custom_class'];
        $cAction = $elementData['custom_action'];

        unset($elementData['custom_class'], $elementData['custom_action'], $elementData['translate']);

        $applyTemplate = isset( $optionalParams['parent'] ) && $optionalParams['parent'] !== 'displayGroup';

        foreach ($elementData as $key => $data)
        {
            $dataType = gettype($data);

            switch ($dataType)
            {
                case 'array':

                    if ($key === 'childs')
                    {
                        $FilVal = $this->buildFilterValidator($data);
                        $options = array_merge($options, $FilVal);
                    }
                    else
                    {
                        $options = $this->addFieldOptions($data, $options, $key);
                    }

                    break;

                default :

                    if ($key !== 'name' && $key !== 'fieldtype')
                    {
                        if ($data != '')
                        {
                            $multipleData = [];

                            if( !in_array( $key, ['label', 'description'] ) )
                            {
                                $multipleData = preg_split('#,#', $data);
                            }

                            if (count( $multipleData ) > 1)
                            {
                                $options[$key] = array();
                                foreach ($multipleData as $val)
                                {
                                    array_push($options[$key], $val);
                                }
                            }
                            else
                            {
                                $options[$key] = $data;
                            }
                        }
                        elseif ($dataType == 'boolean')
                        {
                            $options[$key] = (bool) $data;
                        }
                    }

                    break;
            }
        }

        $options['order'] = $this->subFormCounter;

        if ( count($options) > 0 )
        {
            $config[ $elementData['name'] ]['options'] = $options;
        }

        $config[ $elementData['name'] ]['disableTranslator'] = FALSE;

        //add custom template decorators!
        //if we're elements nested in a displayGroup, skip this part => all templates has been added to the displayGroup since it's the parent element wrapper!
        if( $applyTemplate && $optionalParams['template'] && !empty( $optionalParams['template'] ) )
        {
            if( !isset( $config[ $elementData['name'] ]['options']['additionalDecorators'] ) )
            {
                $config[ $elementData['name'] ]['options']['additionalDecorators'] = [];
            }

            $configTemp = \Formbuilder\Model\Configuration::get('form.area.groupTemplates');

            if( !empty( $configTemp ) && isset( $configTemp[ $optionalParams['template'] ]['elements']['decorators'] ) )
            {
                $config[ $elementData['name'] ]['options']['additionalDecorators'] = array_merge(
                    $config[ $elementData['name'] ]['options']['additionalDecorators'],
                    $configTemp[ $optionalParams['template'] ]['elements']['decorators']
                );
            }
        }

        $this->subFormCounter++;

        $config = $this->fireHook($cClass, $cAction, $config);

        return $config;
    }

    protected function addFieldOptions( $data, $options, $key )
    {
        if( !is_array( $data ) )
        {
            if( !in_array( $key, $this->disallowedFromOptionFields ) )
            {
                $options[ $key ] = $data;
            }

            return $options;
        }

        $multi = $this->buildMultiData($data);

        if ( count( $multi ) === 0 )
        {
            return $options;
        }

        if ( in_array($key, $this->mergeOptionFields) )
        {
            $options = array_merge($options, $multi);
        }
        else
        {
            $options[ $key ] = $multi;
        }

        return $options;
    }

    protected function buildMultiData( $datas )
    {
        $arr = [];

        if( !is_array( $datas ) )
        {
            return $arr;
        }

        foreach ( $datas as $data )
        {
            if ( is_string($data) )
            {
                array_push( $arr, $data );
            }
            else
            {
                $arr[ $data['name'] ] = $data['value'];
            }
        }

        return $arr;
    }

    protected function fireHook($class, $method, $config)
    {
        if ($class != null && $class != '' && $method != null && $method != '')
        {
            if (class_exists($class))
            {
                if (method_exists($class, $method))
                {
                    $refl = new \ReflectionMethod($class, $method);

                    if ($refl->isStatic() && $refl->isPublic())
                    {
                        $ret = $class::$method($config, $this->locale);
                        if (is_array($ret))
                        {
                            $config = $ret;
                        }
                    }
                    elseif (!$refl->isStatic() && $refl->isPublic())
                    {
                        $obj = new $class();
                        $ret = $obj->$method($config, $this->locale);

                        if (is_array($ret))
                        {
                            $config = $ret;
                        }
                    }
                }
            }
        }

        return $config;

    }

    protected function correctArray($datas)
    {
        $ret = array();

        foreach ($datas as $k => $v)
        {
            if (preg_match('#\.#', $k))
            {
                $temp = preg_split('#\.#', $k);
                if (!array_key_exists($temp[0], $ret))
                {
                    $ret[$temp[0]] = array();
                }
                $ret[$temp[0]][$temp[1]] = $v;
            }
            else
            {
                if (is_array($v))
                {
                    $ret[$k] = $this->correctArray($v);
                }
                else
                {
                    $ret[$k] = $v;
                }
            }
        }

        return $ret;
    }

    protected function buildTranslate()
    {
        $this->translations = [];

        foreach ($this->languages as $lang)
        {
            $this->translations[ $lang ] = [];
        }

        foreach ($this->translate as $fieldName => $translateData)
        {
            foreach ($translateData as $key => $value)
            {
                if (substr($key, 0, 8) == 'original')
                {
                    $n = strlen($key);

                    //type = label, description, legend, etc...
                    $fieldType = substr($key, 8, $n - 8);

                    if( isset( $translateData[ $fieldType ] ))
                    {
                        $translations = $translateData[ $fieldType ];

                        if( is_array( $translations ) )
                        {
                            $storeData = [];
                            foreach( $translations as $translation )
                            {
                                $locale     = $translation['name'];
                                $transValue = $translation['value'];

                                $element = [
                                    'locale'    => $locale,
                                    'value'     => $transValue
                                ];

                                $storeData[] = $element;

                            }

                            $this->addTranslate($value, $storeData);
                        }
                    }

                }
                else if ($key == 'multiOptions')
                {
                    if( is_array( $value ) )
                    {
                        foreach( $value as $translation )
                        {
                            $locale         = $translation['name'];
                            $originalValue  = $translation['multiOption'];
                            $transValue     = $translation['value'];

                            $element = [
                                'locale'    => $locale,
                                'value'     => $transValue
                            ];

                            $this->addTranslate($originalValue, [ $element ]);
                        }
                    }
                }
            }
        }

        foreach ($this->translateValidator as $elem)
        {
            $this->translations[ $elem['locale'] ][ $elem['name'] ] = $elem['value'];
        }

        $this->saveTranslations();
    }

    protected function addTranslate($original, $array)
    {
        if( empty( $array ) )
        {
            return FALSE;
        }

        foreach ($array as $elem)
        {
            $this->translations[ $elem['locale'] ][ $original ] = $elem['value'];
        }
    }

    protected function saveTranslations()
    {
        foreach ($this->languages as $lang)
        {
            $path = FORMBUILDER_DATA_PATH . '/lang/form_' . $this->id . '_' . $lang . '.json';

            $config = new \Zend_Config($this->translations[ $lang ], TRUE);

            $writer = new \Zend_Config_Writer_Json(
                array(
                    'config'    => $config,
                    'filename'  => $path
                )
            );

            $writer->setPrettyPrint( TRUE );
            $writer->write();

        }

    }

    protected function buildFilterValidator($datas)
    {
        $iFilter = array();
        $iValidator = array();

        $FilVal = array();
        $filters = array();
        $validators = array();

        foreach ($datas as $data)
        {
            if ($data['isFilter'] == true)
            {
                if (array_key_exists($data['fieldtype'], $iFilter))
                {
                    $iFilter[$data['fieldtype']]++;
                }
                else
                {
                    $iFilter[$data['fieldtype']] = 0;
                }

                $filter = $this->buildFilter($data, $iFilter[$data['fieldtype']]);
                $filters = array_merge($filters, $filter);
            }

            if ($data['isValidator'] == true)
            {
                if (array_key_exists($data['fieldtype'], $iValidator))
                {
                    $iValidator[$data['fieldtype']]++;
                }
                else
                {
                    $iValidator[$data['fieldtype']] = 0;
                }

                $validator = $this->buildValidator($data, $iValidator[$data['fieldtype']]);
                $validators = array_merge($validators, $validator);
            }
        }

        if (count($filters) > 0)
        {
            $FilVal['filters'] = $filters;
        }
        if (count($validators) > 0)
        {
            $FilVal['validators'] = $validators;
        }

        return $FilVal;
    }

    protected function buildFilter($datas, $index)
    {
        $filter = array();
        $filter[$datas['fieldtype'] . $index] = array();
        $filter[$datas['fieldtype'] . $index]['filter'] = $datas['fieldtype'];
        $cClass = $datas['custom_class'];
        $cAction = $datas['custom_action'];

        unset($datas['custom_class'], $datas['custom_action']);

        $options = array();

        foreach ($datas as $key => $data)
        {
            $dataType = gettype($data);

            switch ($dataType) {
                case 'array':

                    $multi = $this->buildMultiData($data);
                    if (count($multi) > 0)
                    {
                        $options[$key] = $multi;
                    }

                    break;
                default :
                    if ($key != 'name' && $key != 'fieldtype' && $key != 'isFilter')
                    {
                        if ($data != '')
                        {
                            $options[$key] = $data;
                        }
                        elseif ($dataType == 'boolean')
                        {
                            $options[$key] = (bool) $data;
                        }
                    }
                    break;
            }
        }

        if (count($options) > 0)
        {
            $filter[$datas['fieldtype'] . $index]['options'] = $options;
        }

        $filter = $this->fireHook($cClass, $cAction, $filter);

        return $filter;
    }

    protected function addValidatorTranslate($datas, $index)
    {
        if (is_array($datas['messages']))
        {
            foreach ($datas['messages'] as $key)
            {
                if ($datas['messages.' . $key] != '')
                {
                    $defaultKeyValue = $datas['messages.' . $key];

                    if (isset( $datas['translate'][ $key ] ) && is_array( $datas['translate'][ $key ] ) )
                    {
                        foreach ($datas['translate'][$key] as $trans)
                        {
                            array_push($this->translateValidator, array(
                                'locale' => $trans['name'],
                                'value' => $trans['value'],
                                'name' => $defaultKeyValue
                            ));
                        }
                    }

                }
            }
        }
    }

    protected function buildValidator($datas, $index)
    {
        $validator = array();
        $validator[$datas['fieldtype'] . $index] = array();
        $validator[$datas['fieldtype'] . $index]['validator'] = $datas['fieldtype'];

        $this->addValidatorTranslate($datas, $index);
        unset($datas['messages']);
        unset($datas['translate']);

        $cClass = $datas['custom_class'];
        $cAction = $datas['custom_action'];
        unset($datas['custom_class']);
        unset($datas['custom_action']);

        $options = array();

        foreach ($datas as $key => $data)
        {
            $dataType = gettype($data);

            switch ($dataType)
            {
                case 'array':

                    $multi = $this->buildMultiData($data);
                    if (count($multi) > 0)
                    {
                        $options[$key] = $multi;
                    }

                    break;

                default :

                    if ($key != 'name' && $key != 'fieldtype' && $key != 'isValidator')
                    {
                        if ($data != '')
                        {
                            $options[$key] = $data;
                        } elseif ($dataType == 'boolean')
                        {
                            $options[$key] = (bool) $data;
                        }
                    }

                    break;
            }
        }

        if (count($options) > 0)
        {
            $validator[$datas['fieldtype'] . $index]['options'] = $options;
        }

        $validator = $this->fireHook($cClass, $cAction, $validator);

        return $validator;
    }

}