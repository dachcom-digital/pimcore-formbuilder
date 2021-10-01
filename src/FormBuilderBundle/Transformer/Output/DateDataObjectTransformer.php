<?php

namespace FormBuilderBundle\Transformer\Output;

use Carbon\Carbon;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class DateDataObjectTransformer implements OutputTransformerInterface
{
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
