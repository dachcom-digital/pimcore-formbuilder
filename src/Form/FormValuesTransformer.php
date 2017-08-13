<?php

namespace FormBuilderBundle\Form;

use FormBuilderBundle\Storage\FormFieldInterface;
use Pimcore\Translation\Translator;
use Symfony\Component\Form\FormInterface;

/**
 * @method getProperty($option)
 * @method hasProperty($option)
 */
class FormValuesTransformer
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * FormTypeOptionsMapper constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param $field
     * @param $formField
     * @param $locale
     *
     * @return mixed
     */
    public function getFieldValue($field, $formField, $locale)
    {
        $value = $field['value'];
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
     * @param               $formData
     * @param               $locale
     *
     * @return array
     */
    public function transformData(FormInterface $form, $formData, $locale)
    {
        $fields = [];

        foreach ($formData as $field) {

            /** @var FormFieldInterface $entityField */
            $entityField = $field['entity_field'];
            $formField = $form->get($entityField->getName());

            $fieldOptions = $entityField->getOptions();
            $fields[$entityField->getOrder()] = [
                'name'        => $entityField->getName(),
                'type'        => $entityField->getType(),
                'label'       => $fieldOptions->hasLabel() ? $this->translator->trans($fieldOptions->getLabel(), [], NULL, $locale) : $entityField->getName(),
                'email_label' => $fieldOptions->hasEmailLabel() ? $this->translator->trans($fieldOptions->getEmailLabel(), [], NULL, $locale) : NULL,
                'value'       => $this->getFieldValue($field, $formField, $locale),
            ];
        }

        return $fields;
    }

}