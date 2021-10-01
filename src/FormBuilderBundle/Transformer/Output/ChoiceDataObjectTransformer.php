<?php

namespace FormBuilderBundle\Transformer\Output;

use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Target\TargetAwareData;
use FormBuilderBundle\Transformer\Target\TargetAwareValue;
use FormBuilderBundle\Transformer\Output\Traits\ChoiceTargetTransformerTrait;

class ChoiceDataObjectTransformer implements OutputTransformerInterface
{
    use ChoiceTargetTransformerTrait;

    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed
    {
        if (!in_array($fieldDefinition->getType(), ['choice', 'dynamic_choice'])) {
            return null;
        }

        return new TargetAwareValue([$this, 'getTargetAwareValue']);
    }

    public function getTargetAwareValue(TargetAwareData $targetAwareData): mixed
    {
        $rawValue = $targetAwareData->getRawValue();
        $validType = is_array($rawValue) || is_scalar($rawValue);

        if ($validType === false) {
            return null;
        }

        $target = $targetAwareData->getTarget();

        return $this->parseChoiceValue($target, $rawValue);
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string
    {
        return null;
    }
}
