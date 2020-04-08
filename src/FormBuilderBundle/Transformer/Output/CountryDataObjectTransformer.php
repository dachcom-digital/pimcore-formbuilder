<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Target\TargetAwareData;
use FormBuilderBundle\Transformer\Target\TargetAwareValue;
use FormBuilderBundle\Transformer\Output\Traits\ChoiceTargetTransformerTrait;
use Symfony\Component\Form\FormInterface;
use FormBuilderBundle\Storage\FormFieldSimpleInterface;

class CountryDataObjectTransformer implements OutputTransformerInterface
{
    use ChoiceTargetTransformerTrait;

    /**
     * {@inheritdoc}
     */
    public function getValue(FormFieldSimpleInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        $type = $fieldDefinition instanceof FieldDefinitionInterface ? $fieldDefinition->getType() : null;

        if ($type !== 'country') {
            return null;
        }

        return new TargetAwareValue([$this, 'getTargetAwareValue']);
    }

    /**
     * @param TargetAwareData $targetAwareData
     *
     * @return mixed|null
     */
    public function getTargetAwareValue(TargetAwareData $targetAwareData)
    {
        $rawValue = $targetAwareData->getRawValue();
        $validType = is_array($rawValue) || is_scalar($rawValue);

        if ($validType === false) {
            return null;
        }

        $target = $targetAwareData->getTarget();

        return $this->parseChoiceValue($target, $rawValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FormFieldSimpleInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
    {
        return null;
    }
}
