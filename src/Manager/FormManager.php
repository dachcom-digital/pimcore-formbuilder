<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormField;

class FormManager
{
    /**
     * @param $id
     *
     * @return Form|null
     */
    public function getById($id)
    {
        return Form::getById($id);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return Form::getAll();
    }

    /**
     * @param $name
     *
     * @return Form|null
     */
    public function getIdByName($name)
    {
        return Form::getByName($name);
    }

    /**
     * @param array $data
     * @param int $id
     *
     * @return Form
     */
    public function save($data, $id = null)
    {
        if ($id) {
            $form = Form::getById($id);
        } else {
            $form = new Form();
        }

        $this->updateFormAttributes($data, $form);
        $this->updateFields($data['form_fields'], $form);
        $form->save();

        return $form;
    }

    /**
     * @param int $id
     *
     * @return Form|null
     */
    public function delete($id)
    {
        $object = Form::getById($id);

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
     * @return Form|null
     */
    public function rename($id, $newName)
    {
        $object = Form::getById($id);

        if (!$object) {
            return null;
        }

        $object->rename($newName);

        return $object;
    }


    /**
     * @param $data
     * @param Form $form
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
     * @param Form $form
     */
    protected function updateFields($data, $form)
    {
        $counter = 0;
        $fields = [];

        foreach ($this->getValue($data, 'fields', []) as $fieldData) {

            $counter++;
            $fieldType = $this->getValue($fieldData, 'type');
            $fieldName = $this->getValue($fieldData, 'name');
            $fieldDisplayName = $this->getValue($fieldData, 'display_name');
            $field = $form->getField($fieldName);

            if (!$field) {
                $field = new FormField();
                $field->setName($fieldName);
            } elseif ($field->getType() !== $fieldType || !$field->getName()) {
                $field->setName($fieldName);
            }

            $field->setDisplayName($fieldDisplayName);
            $field->setOrder($counter);
            $field->setType($fieldType);
            $field->setWidth($this->getValue($fieldData, 'width', 'full'));

            $field->setOptions($this->getFieldOptions($fieldData));

            $fields[] = $field;
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

    protected function getFieldOptions($data)
    {
        $removeKeys = ['display_name', 'name', 'width', 'type', 'fields'];
        return array_diff_key($data, array_flip($removeKeys));
    }

}
