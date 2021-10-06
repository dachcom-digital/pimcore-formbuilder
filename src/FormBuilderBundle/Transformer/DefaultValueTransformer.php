<?php

namespace FormBuilderBundle\Transformer;

class DefaultValueTransformer implements OptionsTransformerInterface
{
    public function transform(array $values, ?array $optionConfig = null): array
    {
        if (!isset($optionConfig['default_value'])) {
            return $values;
        }

        if (empty($values)) {
            return $optionConfig['default_value'];
        }

        return $values;
    }

    public function reverseTransform(array $values, ?array $optionConfig = null): array
    {
        if (!isset($optionConfig['default_value'])) {
            return $values;
        }

        if ($values === $optionConfig['default_value']) {
            return [];
        }

        return $values;
    }
}
