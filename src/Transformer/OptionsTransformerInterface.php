<?php

namespace FormBuilderBundle\Transformer;

interface OptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     */
    public function transform(mixed $values, ?array $optionConfig = null): mixed;

    /**
     * Transform symfony choices array into valid ExtJs Array.
     */
    public function reverseTransform(mixed $values, ?array $optionConfig = null): mixed;
}
