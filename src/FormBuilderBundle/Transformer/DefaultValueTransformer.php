<?php

namespace FormBuilderBundle\Transformer;

class DefaultValueTransformer implements OptionsTransformerInterface
{
    public function transform(array $optionValue, ?array $optionConfig = null): array
    {
        if (!isset($optionConfig['default_value'])) {
            return $optionValue;
        }

        if (empty($optionValue)) {
            return $optionConfig['default_value'];
        }

        return $optionValue;
    }

    public function reverseTransform(array$optionValue, ?array $optionConfig = null): array
    {
        if (!isset($optionConfig['default_value'])) {
            return $optionValue;
        }

        if ($optionValue === $optionConfig['default_value']) {
            return [];
        }

        return $optionValue;
    }
}
