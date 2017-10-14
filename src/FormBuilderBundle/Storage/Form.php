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
    public $id = NULL;

    /**
     * @var null
     */
    public $name = NULL;

    /**
     * @var null
     */
    public $date = NULL;

    /**
     * @var array
     */
    public $config = [];

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
            return NULL;
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
            return NULL;
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
     * @param $newName
     *
     * @return bool
     */
    public function rename($newName)
    {
        $this->setName($newName);
        $this->save();

        return TRUE;
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
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return null
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
     * @param $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $validConfig = array_intersect_key($config, array_flip( self::ALLOWED_FORM_KEYS));
        $this->config = $validConfig;
        return $this;
    }

    /**
     * @param       $name
     * @param       $type
     * @param       $options
     * @param array $optional
     * @return $this
     * @throws \Exception
     */
    public function addDynamicField($name, $type, $options, $optional = [])
    {
        if(in_array($name, Configuration::INVALID_FIELD_NAMES)) {
            throw new \Exception(sprintf('\'%s\' is a reserved form field name used by the form builder bundle and cannot be used.', $name));
        }

        $update = FALSE;
        if(isset($this->fields[$name])) {
            if(!$this->fields[$name] instanceof FormFieldDynamicInterface) {
                throw new \Exception(sprintf('"%s" as field name is already used by form builder fields.', $name));
            } else {
                $update = TRUE;
            }
        }

        $dynamicField = new FormFieldDynamic($name, $type, $options, $optional, $update);
        $this->fields[$name] = $dynamicField;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @throws \Exception
     */
    public function removeDynamicField($name)
    {
        if(!isset($this->fields[$name])) {
            throw new \Exception(sprintf('cannot remove dynamic field, "%s" does not exists', $name));
        }

        if(isset($this->fields[$name]) && $this->fields[$name] instanceof FormFieldDynamicInterface) {
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
     * @param $type
     *
     * @return array
     */
    public function getFieldsByType($type)
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
     * @param $name
     *
     * @return mixed
     */
    public function getField($name)
    {
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }
    }

    /**
     * @param $name
     *
     * @return null
     */
    public function getFieldType($name)
    {
        $field = $this->getField($name);

        if (!$field) {
            return NULL;
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
            return FALSE;
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
     * @return string|array
     */
    public function getFieldValue($name)
    {
        $array = $this->getData();
        if (isset($array[$name])) {
            return $array[$name];
        }
    }

}