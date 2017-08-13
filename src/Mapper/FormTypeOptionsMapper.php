<?php

namespace FormBuilderBundle\Mapper;

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
     * FormTypeOptionsMapper constructor.
     *
     * @param            $options
     */
    public function __construct($options)
    {
        $this->options = $options;
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
            $attributeName = $this->getAttributeName(substr($method, 3));
            if (array_key_exists($attributeName,$this->options)) {
                $val = $this->options[$attributeName];
                return $val;
            }
        } else if (substr($method, 0, 3) === 'has') {
            $attributeName = $this->getAttributeName(substr($method, 3));
            return array_key_exists($attributeName,$this->options) && (!isset($arguments[0]) || ($arguments[0] === TRUE && !empty($this->options[$attributeName])));
        } else if (substr($method, 0, 2) === 'is') {
            $attributeName = $this->getAttributeName(substr($method, 2));
            return array_key_exists($attributeName, $this->options) && is_bool($this->options[$attributeName]) ? $this->options[$attributeName] : FALSE;
        }

        throw new \Exception(sprintf('"%s" is an unavailable option."', $method));
    }

    private function getAttributeName($key)
    {
        return strtolower(
            preg_replace(
                ["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"],
                ["_$1", "_$1_$2"],
                lcfirst($key)
            )
        );

    }
}