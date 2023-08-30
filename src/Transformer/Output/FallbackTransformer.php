<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use Pimcore\Translation\Translator;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\Countries;

class FallbackTransformer implements OutputTransformerInterface
{
    public function __construct(protected Translator $translator)
    {
    }

    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, ?string $locale): mixed
    {
        if ($fieldDefinition instanceof FormFieldDynamicDefinitionInterface) {
            return $this->parseDynamicField($fieldDefinition, $rawValue, $formField, $locale);
        }

        return $this->parseDefaultField($rawValue, $formField, $locale);
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, $rawValue, ?string $locale): ?string
    {
        if ($fieldDefinition instanceof FormFieldDynamicDefinitionInterface) {
            return $this->parseDynamicLabel($fieldDefinition, $formField, $locale);
        }

        return $this->parseDefaultLabel($fieldDefinition, $locale);
    }

    protected function parseDynamicField(FormFieldDynamicDefinitionInterface $field, mixed $rawValue, FormInterface $formField, ?string $locale): mixed
    {
        $optionalOptions = $field->getOptional();

        $valueTransformer = isset($optionalOptions['email_value_transformer']) && is_callable($optionalOptions['email_value_transformer'])
            ? $optionalOptions['email_value_transformer']
            : false;

        $value = false;
        if ($valueTransformer === false) {
            $value = $this->parseDefaultField($rawValue, $formField, $locale);
        } elseif ($valueTransformer instanceof \Closure) {
            $value = call_user_func_array($valueTransformer, [$formField, $rawValue, $locale]);
        } elseif (is_array($valueTransformer)) {
            $value = call_user_func_array($valueTransformer, [$formField, $rawValue, $locale]);
        }

        return $value;
    }

    protected function parseDefaultField(mixed $value, FormInterface $formField, ?string $locale): mixed
    {
        if (empty($value)) {
            return $value;
        }

        $fieldType = $formField->getConfig()->getType()->getInnerType();

        if ($value instanceof \DateTime) {
            return $this->parseDefaultDateField($value, $fieldType, $locale);
        }

        if ($fieldType instanceof CountryType) {
            return $this->parseDefaultCountryField($value, $fieldType, $locale);
        }

        if ($fieldType instanceof ChoiceType) {
            return $this->parseDefaultChoiceField($value, $formField, $fieldType, $locale);
        }

        return $value;
    }

    protected function parseDefaultDateField(\DateTime $value, $fieldType, ?string $locale): string|bool
    {
        $dateFormat = 'medium';
        $timeFormat = 'none';

        if (!class_exists('IntlDateFormatter')) {
            $format = 'm/d/y H:i:s';

            return $value->format($format);
        }

        $formatValues = [
            'none'   => \IntlDateFormatter::NONE,
            'short'  => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long'   => \IntlDateFormatter::LONG,
            'full'   => \IntlDateFormatter::FULL,
        ];

        if ($fieldType instanceof TimeType) {
            $dateFormat = 'none';
        }

        if ($fieldType instanceof DateTimeType || $fieldType instanceof TimeType) {
            $timeFormat = 'medium';
        }

        $formatter = \IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            $value->getTimezone(),
            \IntlDateFormatter::GREGORIAN, // @todo: allow different formatter types (\IntlDateFormatter::TRADITIONAL)?
            null
        );

        return $formatter->format($value->getTimestamp());
    }

    protected function parseDefaultCountryField(mixed $value, CountryType $fieldType, ?string $locale): array|string
    {
        if (is_array($value)) {
            $choices = [];
            foreach ($value as $val) {
                $choices[] = Countries::getName($val, $locale);
            }
        } else {
            $choices = Countries::getName($value, $locale);
        }

        return $choices;
    }

    protected function parseDefaultChoiceField(mixed $value, FormInterface $formField, ChoiceType $fieldType, ?string $locale): array
    {
        $choices = $formField->getConfig()->getOption('choices');
        $arrayIterator = new \RecursiveArrayIterator($choices);

        $choices = [];
        foreach (new \RecursiveIteratorIterator($arrayIterator) as $label => $key) {
            if ((is_array($value) && isset(array_flip($value)[$key])) || $value === $key) {
                $choices[] = $this->translator->trans($label, [], null, $locale);
            }
        }

        return $choices;
    }

    protected function parseDynamicLabel(FormFieldDynamicDefinitionInterface $field, FormInterface $formField, ?string $locale): ?string
    {
        $label = $formField->getConfig()->hasOption('label') ? $formField->getConfig()->getOption('label') : $field->getName();
        $optionalOptions = $field->getOptional();

        $emailLabel = isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
            ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
            : null;

        if (!empty($emailLabel)) {
            return $emailLabel;
        }

        return isset($label) && !empty($label)
            ? $this->translator->trans($label, [], null, $locale)
            : $label;
    }

    protected function parseDefaultLabel(FieldDefinitionInterface $field, ?string $locale): ?string
    {
        if (!$field instanceof FormFieldDefinitionInterface) {
            return null;
        }

        $fieldOptions = $field->getOptions();
        $optionalOptions = $field->getOptional();

        $emailLabel = isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
            ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
            : null;

        if (!empty($emailLabel)) {
            return $emailLabel;
        }

        return isset($fieldOptions['label']) && !empty($fieldOptions['label'])
            ? $this->translator->trans($fieldOptions['label'], [], null, $locale)
            : $field->getName();
    }
}
