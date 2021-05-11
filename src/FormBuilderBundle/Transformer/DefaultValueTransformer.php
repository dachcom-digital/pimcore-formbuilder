<?php

namespace FormBuilderBundle\Transformer;

class DefaultValueTransformer implements OptionsTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($optionValue, $optionConfig = null)
    {
        if (!isset($optionConfig['default_value'])) {
            return $optionValue;
        }

        if (empty($optionValue)) {
            return $optionConfig['default_value'];
        }

        return $optionValue;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($optionValue, $optionConfig = null)
    {
        if (!isset($optionConfig['default_value'])) {
            return $optionValue;
        }

        if ($optionValue === $optionConfig['default_value']) {
            return '';
        }

        return $optionValue;
    }
}
