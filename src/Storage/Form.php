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

    public static function getAll()
    {
        $list = new Form\Listing;
        return $list->getData();
    }

    public static function getNameById($id)
    {
        $obj = new self;
        $obj->getDao()->getById($id);

        return $obj->name;
    }

    public static function getIdByName($name)
    {
        $obj = new self;
        $obj->getDao()->getByName($name);

        return $obj->id;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function rename($newName)
    {
        $this->setName($newName);
        $this->save();

        return TRUE;
    }

    public function save()
    {
        return $this->getDao()->save();
    }

    public function delete()
    {
        return $this->getDao()->delete();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }


    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $validConfig = array_intersect_key($config, array_flip( self::ALLOWED_FORM_KEYS));
        $this->config = $validConfig;

        return $this;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

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

    public function getField($name)
    {
        foreach ($this->fields as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }
    }

    public function getFieldType($name)
    {
        $field = $this->getField($name);

        if (!$field) {
            return;
        }

        return $field->getType();
    }

}