<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Transformer\Target\TargetAwareData;
use FormBuilderBundle\Transformer\Target\TargetAwareValue;
use Pimcore\Model\DataObject\ClassDefinition\Data\Consent;
use Pimcore\Model\Element\Note;
use Symfony\Component\Form\FormInterface;

class CheckboxObjectTransformer implements OutputTransformerInterface
{
    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed
    {
        $type = $fieldDefinition->getType();

        if ($type !== 'checkbox') {
            return $rawValue;
        }

        return new TargetAwareValue([$this, 'getTargetAwareValue']);
    }

    public function getTargetAwareValue(TargetAwareData $targetAwareData): mixed
    {
        $rawValue = $targetAwareData->getRawValue();

        if (!is_bool($rawValue)) {
            return $rawValue;
        }

        $target = $targetAwareData->getTarget();

        if (!$target instanceof Consent) {
            return $rawValue;
        }

        $note = new Note();
        $note->setCtype('object');

        if ($rawValue === true) {
            $note->setType('consent-given');
            $note->setTitle(sprintf('Consent given for field %s', $targetAwareData->getFormField()->getName()));
        } else {
            $note->setType('consent-revoked');
            $note->setTitle(sprintf('Consent revoked for field %s', $targetAwareData->getFormField()->getName()));
        }

        $note->setDate(time());
        $note->setDescription('Added by form builder object mapping');
        $note->save();

        return new \Pimcore\Model\DataObject\Data\Consent($rawValue, $note->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string
    {
        return null;
    }
}
