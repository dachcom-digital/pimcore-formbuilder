<?php

namespace FormBuilderBundle\Transformer;

interface DynamicOptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     */
    public function transform(mixed $rawData, mixed $transformedData, ?array $optionConfig = null): mixed;

    /**
     * Transform symfony choices array into valid ExtJs Array.
     */
    public function reverseTransform(mixed $rawData, mixed $transformedData, ?array $optionConfig = null): mixed;
}
