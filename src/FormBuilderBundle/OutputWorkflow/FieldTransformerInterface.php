<?php

namespace FormBuilderBundle\OutputWorkflow;

interface FieldTransformerInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return null|string
     */
    public function getDescription();

    /**
     * @param mixed $value
     * @param array $context
     *
     * @return mixed
     */
    public function transform($value, array $context);
}
