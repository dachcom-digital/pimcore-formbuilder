<?php

namespace FormBuilderBundle\Transformer\Output;

use Carbon\Carbon;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Input\InputTransformerInterface;
use Symfony\Component\Form\FormInterface;

class DateDataObjectTransformer implements OutputTransformerInterface, InputTransformerInterface
{
    public function getValueReverse(FieldDefinitionInterface $fieldDefinition, mixed $formValue): \DateTime
    {
        if (!in_array($fieldDefinition->getType(), ['date', 'date_time', 'time', 'birthday'])) {
            return $formValue;
        }

        if (!is_string($formValue)) {
            return $formValue;
        }

        return Carbon::parse($formValue)->toDateTime();
    }

    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed
    {
        if (!$rawValue instanceof \DateTime) {
            return null;
        }

        $carbon = Carbon::instance($rawValue);

        if ($fieldDefinition->getType() === 'time') {
            return $carbon->toTimeString('minute');
        }

        return $carbon;
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string
    {
        return null;
    }
}
