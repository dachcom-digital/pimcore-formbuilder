<?php

namespace FormBuilderBundle\Transformer;

interface DynamicOptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     */
    public function transform($rawData, $transformedData, ?array $optionConfig = null): array;

    /**
     * Transform symfony choices array into valid ExtJs Array.
     */
    public function reverseTransform($rawData, $transformedData, ?array $optionConfig = null): array;
}
