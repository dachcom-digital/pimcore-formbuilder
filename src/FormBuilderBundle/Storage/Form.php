<?php

namespace FormBuilderBundle\Storage;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Storage\Form\Dao;
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
     * @var int|null
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var null|string
     */
    public $group;

    /**
     * @var string
     */
    public $creationDate;

    /**
     * @var string
     */
    public $modificationDate;

    /**
     * @var int
     */
    public $modifiedBy;

    /**
     * @var int
     */
    public $createdBy;

    /**
     * @var array
     */
    public $config;

    /**
     * @var array
     */
    public $conditionalLogic;

    /**
     * @var array
     */
    public $fields;

    /**
     * @var array
     */
    private $data;

    /**
     * @inheritdoc
     */
    public static function getById(int $id)
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
     * @inheritdoc
     */
    public static function getByName(string $name)
    {
        if (empty($name)) {
            return null;
        }

        $obj = new self;
        $obj->getDao()->getByName($name);

        return $obj;
    }

    /**
     * @inheritdoc
     */
    public static function getAll()
    {
        $list = new Form\Listing;
        return $list->getData();
    }

    /**
     * @inheritdoc
     */
    public static function getNameById(int $id)
    {
        $obj = new self;
        $obj->getDao()->getById($id);

        return $obj->name;
    }

    /**
     * @inheritdoc
     */
    public static function getIdByName(string $name)
    {
        $obj = new self;
        $obj->getDao()->getByName($name);

        return $obj->id;
    }

    /**
     * @inheritdoc
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function rename(string $newName)
    {
        $this->setName($newName);
        $this->save();
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $this->getDao()->save();
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $this->getDao()->delete();
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return is_numeric($this->id) ? (int)$this->id : null;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(string $groupName = null)
    {
        $this->group = !empty($groupName) && is_string($groupName) ? $groupName : null;
    }

    /**
     * @inheritdoc
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setCreationDate(string $date)
    {
        $this->creationDate = $date;
    }

    /**
     * @inheritdoc
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @inheritdoc
     */
    public function setModificationDate(string $date)
    {
        $this->modificationDate = $date;
    }

    /**
     * @inheritdoc
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @inheritdoc
     */
    public function setModifiedBy(int $userId)
    {
        $this->modifiedBy = $userId;
    }

    /**
     * @inheritdoc
     */
    public function getModifiedBy()
    {
        return (int)$this->modifiedBy;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedBy(int $userId)
    {
        $this->createdBy = $userId;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedBy()
    {
        return (int)$this->createdBy;
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config)
    {
        $validConfig = array_intersect_key($config, array_flip(self::ALLOWED_FORM_KEYS));
        $this->config = $validConfig;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return is_array($this->config) ? $this->config : [];
    }

    /**
     * @inheritdoc
     */
    public function getConditionalLogic()
    {
        return is_array($this->conditionalLogic) ? $this->conditionalLogic : [];
    }

    /**
     * @inheritdoc
     */
    public function setConditionalLogic(array $config)
    {
        $this->conditionalLogic = $config;
    }

    /**
     * @inheritdoc
     */
    public function addDynamicField(string $name, string $type, array $options = [], array $optional = [])
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
    }

    /**
     * @inheritdoc
     */
    public function removeDynamicField(string $name)
    {
        if (!isset($this->fields[$name])) {
            throw new \Exception(sprintf('cannot remove dynamic field, "%s" does not exists', $name));
        }

        if (isset($this->fields[$name]) && $this->fields[$name] instanceof FormFieldDynamicInterface) {
            unset($this->fields[$name]);
        }
    }

    /**
     * @inheritdoc
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    public function getFields()
    {
        return is_array($this->fields) ? $this->fields : [];
    }

    /**
     * @inheritdoc
     */
    public function getFieldsByType(string $type)
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->getType() === $type) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function getField(string $name)
    {
        foreach ($this->getFields() as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getFieldContainer(string $name)
    {
        $fieldContainer = $this->getField($name);
        if ($fieldContainer !== null && !$fieldContainer instanceof FormFieldContainerInterface) {
            throw new \Exception(sprintf('Requested field "%s" container is not an instance of FormFieldContainerInterface', $name));
        }

        return $fieldContainer;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getFieldValue(string $name)
    {
        $array = $this->getData();
        if (isset($array[$name])) {
            return $array[$name];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Dao
     */
    public function getDao()
    {
        return parent::getDao();
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
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }
}