<?php

namespace FormBuilderBundle\Storage;

class Dynamic
{
    /**
     * @var array
     */
    protected static $ARRAY_TYPES = [
        'attachment'
    ];

    /**
     * @var array
     */
    public static $HIDDEN_TYPES = [];

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var array
     */
    private $data;

    /**
     * @param FormInterface $formEntity
     */
    public function __construct($formEntity)
    {
        $this->form = $formEntity;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        if (!is_string($key)) {
            return FALSE;
        }

        $data = $this->getData();

        return isset($data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return $this->getFieldValue($key);
    }

    /**
     * Get field.
     *
     * @param string $key
     *
     * @return string|array
     */
    public function getFieldValue($key)
    {
        $array = $this->getData();
        if (isset($array[$key])) {
            return $array[$key];
        }
    }

    /**
     * @param array $ignoreFields
     *
     * @return array
     */
    public function getFields($ignoreFields = [])
    {
        $fields = [];

        if (!$this->form) {
            return [];
        }

        /** @var FormFieldInterface $field */
        foreach ($this->form->getFields() as $field) {
            if (in_array($field->getType(), self::$HIDDEN_TYPES)) {
                continue;
            }

            if (in_array($field->getName(), $ignoreFields)) {
                continue;
            }

            $fields[] = [
                'entity_field' => $field,
                'value'        => $this->getFieldValue($field->getName()),
            ];
        }

        return $fields;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getFieldsByType($type)
    {
        $entry = [];

        if (!$this->form) {
            return [];
        }

        foreach ($this->form->getFieldsByType($type) as $field) {
            $entry[$field->getKey()] = $this->getField($field->getKey());
        }

        return $entry;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getFieldType($key)
    {
        if (!$this->form) {
            return '';
        }

        return $this->form->getFieldType($key);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
