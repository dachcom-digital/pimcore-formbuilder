<?php

namespace FormBuilderBundle\Transformer;

interface OptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     */
    public function transform(array $values, ?array $optionConfig = null): array;

    /**
     * Transform symfony choices array into valid ExtJs Array.
     */
    public function reverseTransform(array $values, ?array $optionConfig = null): array;
}
