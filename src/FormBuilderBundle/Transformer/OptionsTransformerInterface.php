<?php

namespace FormBuilderBundle\Transformer;

interface OptionsTransformerInterface
{
    /**
     * Transform ExtJs Array to valid symfony choices array.
     *
     * @param array      $values
     * @param null|array $optionConfig
     *
     * @return array
     */
    public function transform($values, $optionConfig = null);

    /**
     * Transform symfony choices array into valid ExtJs Array.
     *
     * @param array      $values
     * @param null|array $optionConfig
     *
     * @return array
     */
    public function reverseTransform($values, $optionConfig = null);
}
