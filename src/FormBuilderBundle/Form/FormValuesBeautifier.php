<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Form\Type\Container\ContainerType;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
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
     * @param FormInterface $form
     * @param array         $ignoreFields
     * @param string        $locale
     *
     * @return array
     */
    public function transformData(FormInterface $form, array $ignoreFields, $locale)
    {
        $fieldValues = [];

        /** @var FormBuilderFormInterface $formEntity */
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
                $data = $this->transformDynamicField($formEntity, $field, $formField, $locale);
                if ($data !== null) {
                    $fieldValues[$field->getName()] = $data;
                }
            } else {
                if ($field instanceof FormFieldContainerInterface) {
                    $data = $this->transformFormBuilderContainerField($formEntity, $field, $formField, $locale);
                    if ($data !== null) {
                        $fieldValues[$field->getName()] = $data;
                    }
                } else {
                    $data = $this->transformFormBuilderField($formEntity, $field, $formField, $locale);
                    if ($data !== null) {
                        $fieldValues[$field->getName()] = $data;
                    }
                }
            }
        }

        return $fieldValues;
    }

    /**
     * @param FormBuilderFormInterface  $formEntity
     * @param FormFieldDynamicInterface $entityField
     * @param FormInterface             $formField
     * @param                           $locale
     *
     * @return array
     */
    private function transformDynamicField(FormBuilderFormInterface $formEntity, FormFieldDynamicInterface $entityField, FormInterface $formField, $locale)
    {
        $formFieldValue = $formEntity->getFieldValue($entityField->getName());
        $data = $this->beautifyDynamicField($entityField, $formFieldValue, $formField, $locale);

        return $data;
    }

    /**
     * @param FormBuilderFormInterface    $formEntity
     * @param FormFieldContainerInterface $entityField
     * @param FormInterface               $formField
     * @param                             $locale
     *
     * @return array|null
     */
    private function transformFormBuilderContainerField(FormBuilderFormInterface $formEntity, FormFieldContainerInterface $entityField, FormInterface $formField, $locale)
    {
        if (
            $formField->getConfig()->getType()->getParent() === null ||
            !$formField->getConfig()->getType()->getParent()->getInnerType() instanceof ContainerType
        ) {
            return null;
        }

        if ($formField->count() === 0) {
            return null;
        }

        $formFieldValues = $formEntity->getFieldValue($entityField->getName());

        $subFieldValues = [];
        foreach ($formField->all() as $index => $subFieldCollection) {
            $subCollectionFieldValues = [];
            foreach ($entityField->getFields() as $subField) {
                $subFormField = $subFieldCollection->get($subField->getName());
                $formFieldValue = is_array($formFieldValues) && isset($formFieldValues[$index][$subField->getName()]) ? $formFieldValues[$index][$subField->getName()] : null;
                $subCollectionFieldValues[] = $this->beautifyFormBuilderField($subField, $formFieldValue, $subFormField, $locale);
            }
            if (count($subCollectionFieldValues) > 0) {
                $subFieldValues[] = $subCollectionFieldValues;
            }
        }

        if (count($subFieldValues) === 0) {
            return null;
        }

        $fieldConfig = $entityField->getConfiguration();
        $containerLabel = isset($fieldConfig['label']) && !empty($fieldConfig['label']) ? $fieldConfig['label'] : false;
        $blockLabel = isset($fieldConfig['block_label']) && !empty($fieldConfig['block_label']) ? $fieldConfig['block_label'] : false;

        $data = [
            'render_type' => 'container',
            'label'       => $containerLabel,
            'block_label' => $blockLabel,
            'type'        => $entityField->getSubType(),
            'fields'      => $subFieldValues
        ];

        return $data;
    }

    /**
     * @param FormBuilderFormInterface $formEntity
     * @param FormFieldInterface       $entityField
     * @param FormInterface            $formField
     * @param                          $locale
     *
     * @return array
     */
    private function transformFormBuilderField(FormBuilderFormInterface $formEntity, FormFieldInterface $entityField, FormInterface $formField, $locale)
    {
        $formFieldValue = $formEntity->getFieldValue($entityField->getName());
        $data = $this->beautifyFormBuilderField($entityField, $formFieldValue, $formField, $locale);
        return $data;
    }

    /**
     * @param FormFieldInterface $field
     * @param mixed              $formFieldValue
     * @param FormInterface      $formField
     * @param string             $locale
     *
     * @return array
     */
    private function beautifyFormBuilderField(FormFieldInterface $field, $formFieldValue, FormInterface $formField, $locale)
    {
        $fieldOptions = $field->getOptions();
        $optionalOptions = $field->getOptional();

        $defaults = [
            'render_type' => 'simple',
            'name'        => $field->getName(),
            'type'        => $field->getType()
        ];

        return array_merge([
            'label'       => isset($fieldOptions['label']) && !empty($fieldOptions['label'])
                ? $this->translator->trans($fieldOptions['label'], [], null, $locale)
                : $field->getName(),
            'email_label' => isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
                ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
                : null,
            'value'       => $this->getFieldValue($formFieldValue, $formField, $locale),
        ], $defaults);
    }

    /**
     * @param FormFieldDynamicInterface $field
     * @param mixed                     $formFieldValue
     * @param FormInterface             $formField
     * @param string                    $locale
     *
     * @return array
     */
    private function beautifyDynamicField(FormFieldDynamicInterface $field, $formFieldValue, FormInterface $formField, $locale)
    {
        $label = $formField->getConfig()->hasOption('label') ? $formField->getConfig()->getOption('label') : $field->getName();
        $optionalOptions = $field->getOptional();

        $valueTransformer = isset($optionalOptions['email_value_transformer']) && is_callable($optionalOptions['email_value_transformer'])
            ? $optionalOptions['email_value_transformer']
            : false;

        $value = false;
        if ($valueTransformer === false) {
            $value = $this->getFieldValue($formFieldValue, $formField, $locale);
        } elseif ($valueTransformer instanceof \Closure) {
            $value = call_user_func_array($valueTransformer, [$formField, $formFieldValue, $locale]);
        } elseif (is_array($valueTransformer)) {
            $value = call_user_func_array($valueTransformer, [$formField, $formFieldValue, $locale]);
        }

        $defaults = [
            'render_type' => 'simple',
            'name'        => $field->getName(),
            'type'        => $field->getType()
        ];

        return array_merge([
            'label'       => isset($label) && !empty($label)
                ? $this->translator->trans($label, [], null, $locale)
                : $label,
            'email_label' => isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
                ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
                : null,
            'value'       => $value
        ], $defaults);
    }

    /**
     * @param mixed         $value
     * @param FormInterface $formField
     * @param string        $locale
     *
     * @return string|array
     */
    public function getFieldValue($value, FormInterface $formField, $locale)
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

}