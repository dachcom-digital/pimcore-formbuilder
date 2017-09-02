<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Storage\FormInterface as FormBuilderFormInterface;
use FormBuilderBundle\Storage\FormFieldDynamicInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use Pimcore\Translation\Translator;
use Symfony\Component\Form\FormInterface;

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
     * @return mixed
     */
    public function getFieldValue($value, $formField, $locale)
    {
        if ($formField->getConfig()->hasOption('choices')) {
            $choices = $formField->getConfig()->getOption('choices');
            $arrayIterator = new \RecursiveArrayIterator($choices);
            $choices = [];
            foreach (new \RecursiveIteratorIterator($arrayIterator) as $label => $key) {
                if ((is_array($value) && isset(array_flip($value)[$key])) || $value === $key) {
                    $choices[] = $this->translator->trans($label, [], NULL, $locale);
                }
            }

            return $choices;
        }

        return $value;
    }

    /**
     * @param FormInterface $form
     * @param               $ignoreFields
     * @param               $locale
     *
     * @return array
     */
    public function transformData(FormInterface $form, $ignoreFields = [], $locale)
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
                ? $this->translator->trans($fieldOptions['label'], [], NULL, $locale)
                : $field->getName(),
            'email_label' => isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
                ? $this->translator->trans($optionalOptions['email_label'], [], NULL, $locale)
                : NULL,
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
        $label = $formField->getConfig()->hasOption('label') ? $formField->getConfig()->hasOption('label') : $field->getName();
        $optionalOptions = $field->getOptional();

        $valueTransformer = isset($optionalOptions['email_value_transformer']) && is_callable($optionalOptions['email_value_transformer'])
            ? $optionalOptions['email_value_transformer']
            : FALSE;

        $value = FALSE;
        $fieldValue = $formEntity->getFieldValue($field->getName());

        if ($valueTransformer === FALSE) {
            $value = $this->getFieldValue($fieldValue, $formField, $locale);
        } else if ($valueTransformer instanceof \Closure) {
            $value = call_user_func_array($valueTransformer, [$formField, $fieldValue, $locale]);
        } else if (is_array($valueTransformer)) {
            $value = call_user_func_array($valueTransformer, [$formField, $fieldValue, $locale]);
        }

        return [
            'label'       => isset($label) && !empty($label)
                ? $this->translator->trans($label, [], NULL, $locale)
                : $label,
            'email_label' => isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
                ? $this->translator->trans($optionalOptions['email_label'], [], NULL, $locale)
                : NULL,
            'value'       => $value

        ];
    }

}