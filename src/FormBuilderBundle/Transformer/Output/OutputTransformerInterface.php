<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Storage\FormFieldSimpleInterface;
use Symfony\Component\Form\FormInterface;

interface OutputTransformerInterface
{
    /**
     * @param FormFieldSimpleInterface $fieldDefinition
     * @param FormInterface            $formField
     * @param mixed                    $rawValue
     * @param string|null              $locale
     *
     * @return mixed
     */
    public function getValue(FormFieldSimpleInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale);

    /**
     * @param FormFieldSimpleInterface $fieldDefinition
     * @param FormInterface            $formField
     * @param mixed                    $rawValue
     * @param string|null              $locale
     *
     * @return mixed
     */
    public function getLabel(FormFieldSimpleInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale);
}
