<?php

namespace Formbuilder\Lib;

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

    public function saveConfig()
    {


        if (file_exists(FORMBUILDER_DATA_PATH . '/form/form_' . $this->id . '.ini'))
        {
            unlink(FORMBUILDER_DATA_PATH . '/form/form_' . $this->id . '.ini');
        }

        $config = new \Zend_Config($this->config, true);
        $writer = new \Zend_Config_Writer_Ini(array(
            'config' => $config,
            'filename' => FORMBUILDER_DATA_PATH . '/form/form_' . $this->id . '.ini'
        ));

        $writer->write();
    }

    protected function createForm()
    {
        $this->translate = array();
        $this->translateValidator = array();

        if (!is_array($this->datas))
        {
            return false;
        }

        $this->config = array();

        $this->config['config']['form'] = array();
        $this->config['config']['form']['action'] = $this->datas['action'];
        $this->config['config']['form']['method'] = $this->datas['method'];
        $this->config['config']['form']['enctype'] = $this->datas['enctype'];

        $multi = $this->buildMultiData($this->datas['attrib']);

        if (count($multi) > 0)
        {
            $this->config['config']['form'] = array_merge($this->config['config']['form'], $multi);
        }

        $this->config['config']['form']['elements'] = array();
        $position = 0;

        if(!isset($this->datas['mainDefinitions']['childs']))
        {
            return FALSE;
        }

        foreach ($this->datas['mainDefinitions']['childs'] as $data) {

            if ($data['fieldtype'] == 'displayGroup')
            {
                if (!is_array($this->config['config']['form']['displayGroups']))
                {
                    $this->config['config']['form']['displayGroups'] = array();
                }

                $ret = $this->buildFieldSet($data, $position);
                $this->config['config']['form']['displayGroups'] = array_merge($this->config['config']['form']['displayGroups'], $ret);
            }
            else
            {
                $ret = $this->buildField($data);
                $this->config['config']['form']['elements'] = array_merge($this->config['config']['form']['elements'], $ret);
            }
            $position++;
        }
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

        $this->saveConfig();

        $this->buildTranslate();

        return true;
    }

    protected function buildTranslate()
    {
        $this->translations = array();

        foreach ($this->languages as $lang)
        {
            $this->translations[$lang] = array();
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
                            $storeData = array();
                            foreach( $translations as $translation )
                            {
                                $locale = $translation['name'];
                                $transValue = $translation['value'];

                                $element = array(
                                    'locale' => $locale,
                                    'value' => $transValue
                                );

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
                        $storeData = array();
                        $originalValue = null;
                        foreach( $value as $translation )
                        {
                            $locale = $translation['name'];
                            $originalValue = $translation['multiOption'];
                            $transValue = $translation['value'];

                            $element = array(
                                'locale' => $locale,
                                'value' => $transValue
                            );

                            $storeData[] = $element;
                            $this->addTranslate($originalValue, $storeData);
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

        foreach ($array as $elem) {

            $this->translations[$elem['locale']][$original] = $elem['value'];
        }
    }

    protected function saveTranslations()
    {
        foreach ($this->languages as $lang)
        {
            if (file_exists(FORMBUILDER_DATA_PATH . '/lang/form_' . $this->id . '_' . $lang . '.csv'))
            {
                unlink(FORMBUILDER_DATA_PATH . '/lang/form_' . $this->id . '_' . $lang . '.csv');
            }

            touch(FORMBUILDER_DATA_PATH . '/lang/form_' . $this->id . '_' . $lang . '.csv');

            $text = '';
            foreach ($this->translations[$lang] as $key => $value)
            {
                $text .= "\"" . mb_strtolower($key) . "\",\"" . $value . "\"\n";
            }

            file_put_contents(FORMBUILDER_DATA_PATH . '/lang/form_' . $this->id . '_' . $lang . '.csv', $text, FILE_TEXT);
        }

    }

    protected function buildFieldSet($datas, $order)
    {
        $config = array();
        $config[$datas['name']] = array();

        $this->translate[$datas['name']] = $datas['translate'];
        unset($datas['translate']);

        $options = array();
        $elements = array();

        foreach ($datas as $key => $data)
        {
            $dataType = gettype($data);

            switch ($dataType)
            {
                case 'array':

                    if ($key == 'childs')
                    {
                        foreach ($data as $elem)
                        {
                            $ret = $this->buildField($elem);
                            $this->config['config']['form']['elements'] = array_merge($this->config['config']['form']['elements'], $ret);

                            $elements[$elem['name']] = $elem['name'];
                        }
                    }
                    else
                    {
                        $multi = $this->buildMultiData($data);
                        if (count($multi) > 0)
                        {
                            if ($key == 'attrib')
                            {
                                $options = array_merge($options, $multi);
                            }
                            else
                            {
                                $options[$key] = $multi;
                            }
                        }
                    }
                    break;

                default :
                    if ($key != 'name' && $key != 'fieldtype')
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

        $options['order'] = $order;

        if (count($options) > 0)
        {
            $config[$datas['name']]['options'] = $options;
        }

        if (count($elements) > 0)
        {
            $config[$datas['name']]['elements'] = $elements;
        }

        return $config;
    }

    protected function buildField($datas)
    {
        $config = array();
        $config[$datas['name']] = array();
        $config[$datas['name']]['type'] = $datas['fieldtype'];

        $this->translate[$datas['name']] = $datas['translate'];
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

                    if ($key == 'childs')
                    {
                        $FilVal = $this->buildFilterValidator($data);
                        $options = array_merge($options, $FilVal);
                    }
                    else
                    {
                        $multi = $this->buildMultiData($data);
                        if (count($multi) > 0)
                        {
                            if ($key == 'attrib')
                            {
                                $options = array_merge($options, $multi);
                            }
                            else
                            {
                                $options[$key] = $multi;
                            }
                        }
                    }

                    break;

                default :

                    if ($key != 'name' && $key != 'fieldtype')
                    {
                        if ($data != '')
                        {
                            $multipl = preg_split('#,#', $data);

                            if (count($multipl) > 1)
                            {
                                $options[$key] = array();
                                foreach ($multipl as $val)
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

        if (count($options) > 0)
        {
            $config[$datas['name']]['options'] = $options;
        }

        $config[$datas['name']]['options']['disableTranslator'] = false;

        $config = $this->fireHook($cClass, $cAction, $config);

        return $config;
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

    protected function buildMultiData($datas)
    {
        $arr = array();

        if( !is_array( $datas ) )
        {
            return $arr;
        }

        foreach ($datas as $data)
        {
            if (is_string($data))
            {
                array_push($arr, $data);
            } else
            {
                $arr[$data['name']] = $data['value'];
            }
        }

        return $arr;

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
        unset($datas['custom_class']);
        unset($datas['custom_action']);

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
                    foreach ($datas['translate'][$key] as $trans)
                    {
                        array_push($this->translateValidator, array(
                            'locale' => $trans['locale'],
                            'value' => $trans['value'],
                            'key' => $datas['messages.' . $key]
                        ));
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