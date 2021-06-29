<?php

namespace FormBuilderBundle\Transformer\Output;

use Carbon\Carbon;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Storage\FormFieldSimpleInterface;

class DateDataObjectTransformer implements OutputTransformerInterface
{
    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        if (!$rawValue instanceof \DateTime) {
            return null;
        }

        $type = $fieldDefinition instanceof FieldDefinitionInterface ? $fieldDefinition->getType() : null;
        $carbon = Carbon::instance($rawValue);

        if ($type === 'time') {
            return $carbon->toTimeString('minute');
        }

        return $carbon;
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        return null;
    }
}
