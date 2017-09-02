<?php

namespace FormBuilderBundle\Storage;

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
     *
     * @return $this
     * @throws \Exception
     */
    public function addDynamicField($name, $type, $options, $optional = [])
    {
        if(isset($this->fields[$name])) {
            throw new \Exception(sprintf('"%s" as form field name is already in use', $name));
        }

        $d = new FormFieldDynamic($name, $type, $options, $optional);
        $this->fields[$name] = $d;
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
     * @return mixed
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