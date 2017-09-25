<?php

namespace FormBuilderBundle\Transformer;

class DefaultValueTransformer implements OptionsTransformerInterface {

    /**
     * Transform empty option value to default value.
     *
     * @param $optionValue
     * @param $optionConfig
     *
     * @return mixed
     */
    public function transform($optionValue, $optionConfig = NULL)
    {
        if(!isset($optionConfig['config']['default_value'])) {
            return $optionValue;
        }

        if(empty($optionValue)) {
            return $optionConfig['config']['default_value'];
        }

        return $optionValue;
    }

    /**
     * Transform default option value into empty value (since a default value only applies to empty options)
     *
     * @param $optionValue
     * @param $optionConfig
     *
     * @return mixed
     */
    public function reverseTransform($optionValue, $optionConfig = NULL)
    {
        if(!isset($optionConfig['config']['default_value'])) {
            return $optionValue;
        }

        if($optionValue === $optionConfig['config']['default_value']) {
            return '';
        }

        return $optionValue;

    }
}