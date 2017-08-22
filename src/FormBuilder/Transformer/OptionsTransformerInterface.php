<?php

namespace FormBuilderBundle\Transformer;

interface OptionsTransformerInterface {

    public function transform($values);

    public function reverseTransform($values);
}