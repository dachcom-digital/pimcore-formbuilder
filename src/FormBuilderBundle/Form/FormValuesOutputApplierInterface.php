<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormInterface;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
interface FormValuesOutputApplierInterface
{
    const FIELD_TYPE_SIMPLE = 'simple';

    const FIELD_TYPE_CONTAINER = 'container';

    /**
     * @param FormInterface $form
     * @param array         $ignoreFields
     * @param string        $channel
     * @param string        $locale
     *
     * @return array
     */
    public function applyForChannel(FormInterface $form, array $ignoreFields, string $channel, $locale);
}
