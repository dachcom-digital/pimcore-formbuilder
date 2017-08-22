<?php

namespace FormBuilderBundle\Form;

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
     * @param $value
     * @param $formField
     * @param $locale
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

        /** @var FormFieldInterface $field */
        foreach ($formEntity->getFields() as $field) {

            if (in_array($field->getName(), $ignoreFields)) {
                continue;
            }

            $formField = $form->get($field->getName());

            $fieldOptions = $field->getOptions();
            $optionalOptions = $field->getOptional();

            $fields[$field->getOrder()] = [
                'name'        => $field->getName(),
                'type'        => $field->getType(),
                'label'       => isset($fieldOptions['label']) ? $this->translator->trans($fieldOptions['label'], [], NULL, $locale) : $field->getName(),
                'email_label' => isset($optionalOptions['email_label']) ? $this->translator->trans($optionalOptions['email_label'], [], NULL, $locale) : NULL,
                'value'       => $this->getFieldValue($formEntity->getFieldValue($field->getName()), $formField, $locale),
            ];
        }

        return $fields;
    }

}