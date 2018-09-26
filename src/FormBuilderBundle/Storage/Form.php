<?php

namespace FormBuilderBundle\Storage;

use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Model;
use Pimcore\Translation\Translator;

class Form extends Model\AbstractModel implements FormInterface
{
    const ALLOWED_FORM_KEYS = [
        'action',
        'method',
        'enctype',
        'noValidate',
        'useAjax',
        'attributes'
    ];

    /**
     * @var
     */
    protected $table;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var null
     */
    public $id = null;

    /**
     * @var null
     */
    public $name = null;

    /**
     * @var null
     */
    public $date = null;

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var array
     */
    public $conditionalLogic = [];

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var array
     */
    private $data;

    /**
     * @param $id
     *
     * @return Form|null
     */
    public static function getById($id)
    {
        $id = intval($id);

        if ($id < 1) {
            return null;
        }

        $obj = new self;
        $obj->getDao()->getById($id);

        return $obj;
    }

    /**
     * @param $name
     *
     * @return Form|null
     */
    public static function getByName($name)
    {
        $name = (string)$name;

        if (empty($name)) {
            return null;
        }

        $obj = new self;
        $obj->getDao()->getByName($name);

        return $obj;
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        $list = new Form\Listing;

        return $list->getData();
    }

    /**
     * @param $id
     *
     * @return null
     */
    public static function getNameById($id)
    {
        $obj = new self;
        $obj->getDao()->getById($id);

        return $obj->name;
    }

    /**
     * @param $name
     *
     * @return null
     */
    public static function getIdByName($name)
    {
        $obj = new self;
        $obj->getDao()->getByName($name);

        return $obj->id;
    }

    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $newName
     *
     * @return bool
     */
    public function rename(string $newName)
    {
        $this->setName($newName);
        $this->save();

        return true;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return $this->getDao()->save();
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        return $this->getDao()->delete();
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $validConfig = array_intersect_key($config, array_flip(self::ALLOWED_FORM_KEYS));
        $this->config = $validConfig;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditionalLogic()
    {
        return $this->conditionalLogic;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConditionalLogic(array $config)
    {
        $this->conditionalLogic = $config;

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $optional
     *
     * @return $this
     * @throws \Exception
     */
    public function addDynamicField(string $name, string $type, array $options, array $optional = [])
    {
        if (in_array($name, Configuration::INVALID_FIELD_NAMES)) {
            throw new \Exception(sprintf('\'%s\' is a reserved form field name used by the form builder bundle and cannot be used.', $name));
        }

        $update = false;
        if (isset($this->fields[$name])) {
            if (!$this->fields[$name] instanceof FormFieldDynamicInterface) {
                throw new \Exception(sprintf('"%s" as field name is already used by form builder fields.', $name));
            } else {
                $update = true;
            }
        }

        $dynamicField = new FormFieldDynamic($name, $type, $options, $optional, $update);
        $this->fields[$name] = $dynamicField;

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     * @throws \Exception
     */
    public function removeDynamicField($name)
    {
        if (!isset($this->fields[$name])) {
            throw new \Exception(sprintf('cannot remove dynamic field, "%s" does not exists', $name));
        }

        if (isset($this->fields[$name]) && $this->fields[$name] instanceof FormFieldDynamicInterface) {
            unset($this->fields[$name]);
        }

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getFieldsByType(string $type)
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->getType() === $type) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param string $name
     *
     * @return FormField|null
     */
    public function getField(string $name)
    {
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getFieldType(string $name)
    {
        $field = $this->getField($name);

        if (!$field) {
            return null;
        }

        return $field->getType();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $data = $this->getData();

        return isset($data[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }

    /**
     * Get field.
     *
     * @param string $name
     *
     * @return string|array|null
     */
    public function getFieldValue(string $name)
    {
        $array = $this->getData();

        if (isset($array[$name])) {
            return $array[$name];
        }

        return null;
    }

}
