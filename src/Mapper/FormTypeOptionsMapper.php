<?php

namespace FormBuilderBundle\Mapper;

use Pimcore\Translation\Translator;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
class FormTypeOptionsMapper
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * FormTypeOptionsMapper constructor.
     *
     * @param            $options
     * @param Translator $translator
     */
    public function __construct($options, Translator $translator)
    {
        $this->options = $options;
        $this->translator = $translator;
    }

    /**
     * @param $method
     * @param $arguments [$ignoreEmpty = false]
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 3) === 'get') {
            $attributeName = lcfirst(substr($method, 3));
            if (array_key_exists($attributeName,$this->options)) {
                $val = $this->options[$attributeName];
                $doTranslate = isset($arguments[0]) && $arguments[0] === TRUE;
                return $doTranslate ? $this->translator->trans($val) : $val;
            }
        } else if (substr($method, 0, 3) === 'has') {
            $attributeName = lcfirst(substr($method, 3));
            return array_key_exists($attributeName,$this->options) && (!isset($arguments[0]) || ($arguments[0] === TRUE && !empty($this->options[$attributeName])));
        } else if (substr($method, 0, 2) === 'is') {
            $attributeName = lcfirst(substr($method, 2));
            return array_key_exists($attributeName, $this->options) && is_bool($this->options[$attributeName]) ? $this->options[$attributeName] : FALSE;
        }

        throw new \Exception(sprintf('"%s" is an unavailable option."', $method));
    }
}