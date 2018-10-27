<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Storage\FormInterface as FormBuilderFormInterface;
use FormBuilderBundle\Storage\FormFieldDynamicInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use Pimcore\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Intl\Intl;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
class FormValuesBeautifier
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * FormValuesTransformer constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param mixed         $value
     * @param FormInterface $formField
     * @param string        $locale
     *
     * @return string|array
     */
    public function getFieldValue($value, $formField, $locale)
    {
        if (empty($value)) {
            return $value;
        }

        $fieldType = $formField->getConfig()->getType()->getInnerType();

        if ($value instanceof \DateTime) {
            if (class_exists('IntlDateFormatter')) {
                $calendar = 'gregorian';
                $formatValues = [
                    'none'   => \IntlDateFormatter::NONE,
                    'short'  => \IntlDateFormatter::SHORT,
                    'medium' => \IntlDateFormatter::MEDIUM,
                    'long'   => \IntlDateFormatter::LONG,
                    'full'   => \IntlDateFormatter::FULL,
                ];

                $dateFormat = 'medium';
                $timeFormat = 'none';
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
                    \IntlTimeZone::createTimeZone($value->getTimezone()->getName()),
                    'gregorian' === $calendar ? \IntlDateFormatter::GREGORIAN : \IntlDateFormatter::TRADITIONAL,
                    null
                );
                return $formatter->format($value->getTimestamp());
            }

            $format = 'm/d/y H:i:s';
            return $value->format($format);

        }

        if ($fieldType instanceof CountryType) {
            if (is_array($value)) {
                $choices = [];
                foreach ($value as $val) {
                    $choices[] = Intl::getRegionBundle()->getCountryName($val, $locale);
                }
            } else {
                $choices = Intl::getRegionBundle()->getCountryName($value, $locale);
            }
            return $choices;
        } elseif ($fieldType instanceof ChoiceType) {
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

        return $value;
    }

    /**
     * @param FormInterface $form
     * @param array         $ignoreFields
     * @param string        $locale
     *
     * @return array
     */
    public function transformData(FormInterface $form, array $ignoreFields, $locale)
    {
        $fields = [];

        /** @var \FormBuilderBundle\Storage\FormInterface $formEntity */
        $formEntity = $form->getData();

        $orderedFields = $formEntity->getFields();
        usort($orderedFields, function ($a, $b) {
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        /** @var FormFieldInterface $field */
        foreach ($orderedFields as $field) {

            if (in_array($field->getName(), $ignoreFields)) {
                continue;
            }

            $formField = $form->get($field->getName());

            if ($field instanceof FormFieldDynamicInterface) {
                $additional = $this->beautifyDynamicField($field, $formEntity, $formField, $locale);
            } else {
                $additional = $this->beautifyFormBuilderField($field, $formEntity, $formField, $locale);
            }

            $fieldData = array_merge(
                [
                    'name' => $field->getName(),
                    'type' => $field->getType(),
                ],
                $additional
            );;

            $fields[] = $fieldData;
        }

        return $fields;
    }

    /**
     * @param FormFieldInterface       $field
     * @param FormBuilderFormInterface $formEntity
     * @param FormInterface            $formField
     * @param string                   $locale
     *
     * @return array
     */
    private function beautifyFormBuilderField(FormFieldInterface $field, FormBuilderFormInterface $formEntity, FormInterface $formField, $locale)
    {
        $fieldOptions = $field->getOptions();
        $optionalOptions = $field->getOptional();

        return [
            'label'       => isset($fieldOptions['label']) && !empty($fieldOptions['label'])
                ? $this->translator->trans($fieldOptions['label'], [], null, $locale)
                : $field->getName(),
            'email_label' => isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
                ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
                : null,
            'value'       => $this->getFieldValue($formEntity->getFieldValue($field->getName()), $formField, $locale),
        ];
    }

    /**
     * @param FormFieldDynamicInterface $field
     * @param FormBuilderFormInterface  $formEntity
     * @param FormInterface             $formField
     * @param string                    $locale
     *
     * @return array
     */
    private function beautifyDynamicField(FormFieldDynamicInterface $field, FormBuilderFormInterface $formEntity, FormInterface $formField, $locale)
    {
        $label = $formField->getConfig()->hasOption('label') ? $formField->getConfig()->getOption('label') : $field->getName();
        $optionalOptions = $field->getOptional();

        $valueTransformer = isset($optionalOptions['email_value_transformer']) && is_callable($optionalOptions['email_value_transformer'])
            ? $optionalOptions['email_value_transformer']
            : false;

        $value = false;
        $fieldValue = $formEntity->getFieldValue($field->getName());

        if ($valueTransformer === false) {
            $value = $this->getFieldValue($fieldValue, $formField, $locale);
        } elseif ($valueTransformer instanceof \Closure) {
            $value = call_user_func_array($valueTransformer, [$formField, $fieldValue, $locale]);
        } elseif (is_array($valueTransformer)) {
            $value = call_user_func_array($valueTransformer, [$formField, $fieldValue, $locale]);
        }

        return [
            'label'       => isset($label) && !empty($label)
                ? $this->translator->trans($label, [], null, $locale)
                : $label,
            'email_label' => isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
                ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
                : null,
            'value'       => $value
        ];
    }
}