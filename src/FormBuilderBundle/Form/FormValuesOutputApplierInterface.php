<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormInterface;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
interface FormValuesOutputApplierInterface
{
    public const FIELD_TYPE_SIMPLE = 'simple';
    public const FIELD_TYPE_CONTAINER = 'container';

    public function applyForChannel(FormInterface $form, array $ignoreFields, string $channel, ?string $locale): array;
}
