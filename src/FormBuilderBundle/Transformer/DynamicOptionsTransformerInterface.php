<?php

namespace FormBuilderBundle\Transformer;

interface DynamicOptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     *
     * @param mixed      $rawData
     * @param mixed      $transformedData
     * @param null|array $optionConfig
     *
     * @return array
     */
    public function transform($rawData, $transformedData, $optionConfig = null);

    /**
     * Transform symfony choices array into valid ExtJs Array.
     *
     * @param mixed      $rawData
     * @param mixed      $transformedData
     * @param null|array $optionConfig
     *
     * @return array
     */
    public function reverseTransform($rawData, $transformedData, $optionConfig = null);
}
