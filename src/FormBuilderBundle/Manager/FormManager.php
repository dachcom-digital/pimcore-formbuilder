<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Factory\FormFactoryInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;

class FormManager
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * FormManager constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param $id
     *
     * @return FormInterface|null
     */
    public function getById($id)
    {
        return $this->formFactory->getFormById($id);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->formFactory->getAllForms();
    }

    /**
     * @param $name
     *
     * @return FormInterface|null
     */
    public function getIdByName($name)
    {
        return $this->formFactory->getFormIdByName($name);
    }

    /**
     * @param array $data
     * @param int $id
     *
     * @return FormInterface
     */
    public function save($data, $id = null)
    {
        if ($id) {
            $form = $this->getById($id);
        } else {
            $form = $this->formFactory->createForm();
        }

        $this->updateFormAttributes($data, $form);
        $this->updateFields($data['form_fields'], $form);
        $form->save();

        return $form;
    }

    /**
     * @param int $id
     *
     * @return FormInterface|null
     */
    public function delete($id)
    {
        $object = $this->getById($id);

        if (!$object) {
            return null;
        }

        $object->delete();

        return $object;
    }

    /**
     * @param int $id
     * @param string $newName
     *
     * @return FormInterface|null
     */
    public function rename($id, $newName)
    {
        $object = $this->getById($id);

        if (!$object) {
            return null;
        }

        $object->rename($newName);

        return $object;
    }


    /**
     * @param $data
     * @param FormInterface $form
     */
    protected function updateFormAttributes($data, $form)
    {
        $form->setName($data['form_name']);
        $form->setDate($data['form_date']);

        if(isset($data['form_config'])) {
            $form->setConfig($data['form_config']);
        }
    }

    /**
     * Updates the contained fields in the form.
     *
     * @param array $data
     * @param FormInterface $form
     */
    protected function updateFields($data, $form)
    {
        $counter = 0;
        $fields = [];

        foreach ($this->getValue($data, 'fields', []) as $fieldData) {

            //allow some space for dynamic fields.
            $counter += 100;

            $fieldType = $this->getValue($fieldData, 'type');

            $optionsParameter = $this->getValue($fieldData, 'options');
            $optionalParameter = $this->getValue($fieldData, 'optional');
            $fieldName = $this->getValue($fieldData, 'name');
            $constraints = $this->getValue($fieldData, 'constraints');

            /** @var FormFieldInterface $field */
            $field = $form->getField($fieldName);

            if (!$field) {
                $field = $this->formFactory->createFormField();
                $field->setName($fieldName);
            } elseif ($field->getType() !== $fieldType || !$field->getName()) {
                $field->setName($fieldName);
            }

            $field->setDisplayName($this->getValue($fieldData, 'display_name'));
            $field->setOrder($counter);
            $field->setType($fieldType);

            $field->setOptions($optionsParameter);
            $field->setOptional($optionalParameter);
            $field->setConstraints($constraints);

            $fields[$fieldName] = $field;
        }

        $form->setFields($fields);
    }

    /**
     * @param array $data
     * @param string $value
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getValue($data, $value, $default = null)
    {
        if (isset($data[$value])) {
            return $data[$value];
        }

        return $default;
    }
}
