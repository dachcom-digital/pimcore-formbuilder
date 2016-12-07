<?php

namespace Formbuilder\Zend\Form\Element;

class Html5Text extends \Zend_Form_Element_Text
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'html5FormText';

    /**
     *
     * Constants that are used for types of elements
     *
     * @var string
     */
    const DEFAULT_TYPE = 'text';
    const FIELD_EMAIL = 'email';
    const FIELD_EMAIL_ADDRESS = 'emailaddress';
    const FIELD_URL = 'url';
    const FIELD_NUMBER = 'number';
    const FIELD_RANGE = 'range';
    const FIELD_DATE = 'date';
    const FIELD_MONTH = 'month';
    const FIELD_WEEK = 'week';
    const FIELD_TIME = 'time';
    const FIELD_DATE_TIME_LOCAL = 'datetime-local';

    /**
     * Mapping of key => value pairs for the elements
     *
     * @var array
     */
    protected static $_mapping = [
        self::FIELD_EMAIL           => 'email',
        self::FIELD_EMAIL_ADDRESS   => 'email',
        self::FIELD_URL             => 'url',
        self::FIELD_NUMBER          => 'number',
        self::FIELD_RANGE           => 'range',
        self::FIELD_DATE            => 'date',
        self::FIELD_MONTH           => 'month',
        self::FIELD_WEEK            => 'week',
        self::FIELD_TIME            => 'time',
        self::FIELD_DATE_TIME_LOCAL => 'datetime-local'
    ];

    /**
     * Check if the validators should be auto loaded
     *
     * @var bool
     */
    private $_autoloadValidators = TRUE;

    /**
     * Check if the filters should be auto loaded
     *
     * @var bool
     */
    private $_autoloadFilters = TRUE;

    /**
     * @param $spec
     * @param $options
     * @uses Zend_Form_Element
     */
    public function __construct($spec, $options = null)
    {
        if ( isset($options['inputType']) && !empty( $options['inputType'] ) && $options['inputType'] !== 'default' )
        {
            $options['type'] = $this->_getType( $options['inputType'] );
            unset( $options['inputType'] );
        }

        parent::__construct($spec, $options);
    }

    /**
     * Flag if the the validators should be auto loaded
     *
     * @param bool $flag
     * @return Html5Text Provides a fluent interface
     */
    public function setAutoloadValidators($flag)
    {
        $this->_autoloadValidators = (bool) $flag;
        return $this;
    }

    /**
     * Flag if the the validators should be auto loaded
     *
     * @return bool
     */
    public function isAutoloadValidators()
    {
        return $this->_autoloadValidators;
    }

    /**
     * Flag if the the filters should be auto loaded
     *
     * @param bool $flag
     * @return Html5Text Provides a fluent interface
     */
    public function setAutoloadFilters($flag)
    {
        $this->_autoloadFilters = (bool) $flag;
        return $this;
    }

    /**
     * Flag if the the validators should be auto loaded
     *
     * @return bool
     */
    public function isAutoloadFilters()
    {
        return $this->_autoloadFilters;
    }

    /**
     * Check if the given type is specified in the mapping and use it if it's available
     * Else return the constant DEFAULT_TYPE value
     *
     * @param $spec
     * @return string
     */
    private function _getType($spec)
    {
        if (array_key_exists(strtolower($spec), self::$_mapping))
        {
            return self::$_mapping[$spec];
        }
        return self::DEFAULT_TYPE;
    }
}