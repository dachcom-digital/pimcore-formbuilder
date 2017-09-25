<?php

namespace FormBuilderBundle\Transformer;

interface OptionsTransformerInterface {

    public function transform($values, $optionConfig = NULL);

    public function reverseTransform($values, $optionConfig = NULL);
}