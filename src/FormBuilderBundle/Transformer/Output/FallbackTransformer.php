<?php

namespace FormBuilderBundle\Transformer\Output;

use Pimcore\Translation\Translator;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDynamicDefinitionInterface;
use FormBuilderBundle\Storage\FormFieldSimpleInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Intl\Intl;

class FallbackTransformer implements OutputTransformerInterface
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        if ($field instanceof FormFieldDynamicDefinitionInterface) {
            return $this->parseDynamicField($field, $rawValue, $formField, $locale);
        }

        return $this->parseDefaultField($rawValue, $formField, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FormFieldSimpleInterface $field, FormInterface $formField, $rawValue, $locale)
    {
        if ($field instanceof FormFieldDynamicDefinitionInterface) {
            return $this->parseDynamicLabel($field, $formField, $locale);
        }

        return $this->parseDefaultLabel($field, $locale);
    }

    /**
     * @param FormFieldDynamicDefinitionInterface $field
     * @param mixed                               $rawValue
     * @param FormInterface                       $formField
     * @param null|string                         $locale
     *
     * @return mixed
     */
    protected function parseDynamicField(FormFieldDynamicDefinitionInterface $field, $rawValue, FormInterface $formField, $locale)
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

    /**
     * @param mixed         $value
     * @param FormInterface $formField
     * @param null|string   $locale
     *
     * @return mixed
     */
    protected function parseDefaultField($value, FormInterface $formField, $locale)
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

    /**
     * @param \DateTime         $value
     * @param FormTypeInterface $fieldType
     * @param string            $locale
     *
     * @return bool|string
     */
    protected function parseDefaultDateField(\DateTime $value, $fieldType, $locale)
    {
        $dateFormat = 'medium';
        $timeFormat = 'none';

        if (!class_exists('IntlDateFormatter')) {
            $format = 'm/d/y H:i:s';

            return $value->format($format);
        }

        $calendar = 'gregorian';
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
            \IntlTimeZone::createTimeZone($value->getTimezone()->getName())->getID(),
            'gregorian' === $calendar ? \IntlDateFormatter::GREGORIAN : \IntlDateFormatter::TRADITIONAL,
            null
        );

        return $formatter->format($value->getTimestamp());
    }

    /**
     * @param mixed       $value
     * @param CountryType $fieldType
     * @param string      $locale
     *
     * @return array|string
     */
    protected function parseDefaultCountryField($value, CountryType $fieldType, $locale)
    {
        if (is_array($value)) {
            $choices = [];
            foreach ($value as $val) {
                $choices[] = Intl::getRegionBundle()->getCountryName($val, $locale);
            }
        } else {
            $choices = Intl::getRegionBundle()->getCountryName($value, $locale);
        }

        return $choices;
    }

    /**
     * @param mixed         $value
     * @param FormInterface $formField
     * @param ChoiceType    $fieldType
     * @param string        $locale
     *
     * @return array
     */
    protected function parseDefaultChoiceField($value, FormInterface $formField, ChoiceType $fieldType, $locale)
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

    /**
     * @param FormFieldDynamicDefinitionInterface $field
     * @param FormInterface                       $formField
     * @param null|string                         $locale
     *
     * @return string|null
     */
    protected function parseDynamicLabel(FormFieldDynamicDefinitionInterface $field, FormInterface $formField, $locale)
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

    /**
     * @param FormFieldSimpleInterface $field
     * @param null|string              $locale
     *
     * @return string|null
     */
    protected function parseDefaultLabel(FormFieldSimpleInterface $field, $locale)
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
