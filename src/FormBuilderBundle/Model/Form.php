<?php

namespace FormBuilderBundle\Model;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldDynamic;
use FormBuilderBundle\Storage\FormFieldDynamicInterface;

class Form extends \FormBuilderBundle\Storage\Form implements FormInterface
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var null|string
     */
    protected $group;

    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @var \DateTime
     */
    protected $modificationDate;

    /**
     * @var int
     */
    protected $modifiedBy;

    /**
     * @var int
     */
    protected $createdBy;

    /**
     * @var array
     */
    protected $mailLayout;

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
    protected $data = [];

    /**
     * @var array
     */
    protected $attachments = [];

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return is_numeric($this->id) ? (int) $this->id : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(string $groupName = null)
    {
        $this->group = !empty($groupName) && is_string($groupName) ? $groupName : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->creationDate = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setModificationDate(\DateTime $date)
    {
        $this->modificationDate = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setModifiedBy(int $userId)
    {
        $this->modifiedBy = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedBy()
    {
        return (int) $this->modifiedBy;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedBy(int $userId)
    {
        $this->createdBy = $userId;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedBy()
    {
        return (int) $this->createdBy;
    }

    /**
     * {@inheritdoc}
     */
    public function setMailLayout($mailLayout = null)
    {
        $this->mailLayout = $mailLayout;
    }

    /**
     * {@inheritdoc}
     */
    public function getMailLayout()
    {
        return $this->mailLayout;
    }

    /**
     * {@inheritdoc}
     */
    public function getMailLayoutBasedOnLocale(string $mailType, string $locale = null)
    {
        $mailLayout = $this->getMailLayout();
        if (is_null($mailLayout)) {
            return null;
        }

        if (!isset($mailLayout[$mailType])) {
            return null;
        }

        if (isset($mailLayout[$mailType][$locale])) {
            return $mailLayout[$mailType][$locale];
        }

        if (isset($mailLayout[$mailType]['default'])) {
            return $mailLayout[$mailType]['default'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config)
    {
        $validConfig = array_intersect_key($config, array_flip(self::ALLOWED_FORM_KEYS));
        $this->config = $validConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionalLogic()
    {
        return $this->conditionalLogic;
    }

    /**
     * {@inheritdoc}
     */
    public function setConditionalLogic(array $config)
    {
        $this->conditionalLogic = $config;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setFieldValue(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttachments()
    {
        return count($this->attachments) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(array $attachmentFileInfo)
    {
        $this->attachments[] = $attachmentFileInfo;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
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
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }
}