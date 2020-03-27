<?php

namespace FormBuilderBundle\Transformer\Output;

use Carbon\Carbon;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Storage\FormFieldSimpleInterface;

class DateObjectTransformer implements OutputTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        if (!$rawValue instanceof \DateTime) {
           return null;
        }

        $type = $field->getType();
        $carbon = Carbon::instance($rawValue);

        if($type === 'time') {
            return $carbon->toTimeString('minute');
        }

        return $carbon;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        return null;
    }
}
