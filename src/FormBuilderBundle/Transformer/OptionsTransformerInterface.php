<?php

namespace FormBuilderBundle\Transformer;

interface OptionsTransformerInterface
{
    public function transform($values, $optionConfig = null);

    public function reverseTransform($values, $optionConfig = null);
}
