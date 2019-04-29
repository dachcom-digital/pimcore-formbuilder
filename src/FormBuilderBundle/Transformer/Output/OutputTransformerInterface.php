<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Storage\FormFieldSimpleInterface;
use Symfony\Component\Form\FormInterface;

interface OutputTransformerInterface
{
    /**
     * @param FormFieldSimpleInterface $field
     * @param FormInterface            $formField
     * @param mixed                    $rawValue
     * @param string|null              $locale
     *
     * @return mixed
     */
    public function getValue(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale);

    /**
     * @param FormFieldSimpleInterface $field
     * @param FormInterface            $formField
     * @param mixed                    $rawValue
     * @param string|null              $locale
     *
     * @return mixed
     */
    public function getLabel(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale);
}
