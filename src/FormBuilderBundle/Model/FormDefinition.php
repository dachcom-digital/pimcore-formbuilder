<?php

namespace FormBuilderBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FormBuilderBundle\Configuration\Configuration;

class FormDefinition implements FormDefinitionInterface
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
     * @var Collection|OutputWorkflowInterface[]
     */
    protected $outputWorkflows;

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

    public function __construct()
    {
        $this->outputWorkflows = new ArrayCollection();
    }

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
    public function hasOutputWorkflows()
    {
        return !$this->outputWorkflows->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function hasOutputWorkflow(OutputWorkflowInterface $outputWorkflow)
    {
        return $this->outputWorkflows->contains($outputWorkflow);
    }

    /**
     * {@inheritdoc}
     */
    public function addOutputWorkflow(OutputWorkflowInterface $outputWorkflow)
    {
        if (!$this->hasOutputWorkflow($outputWorkflow)) {
            $this->outputWorkflows->add($outputWorkflow);
            $outputWorkflow->setFormDefinition($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeOutputWorkflow(OutputWorkflowInterface $outputWorkflow)
    {
        if ($this->hasOutputWorkflow($outputWorkflow)) {
            $this->outputWorkflows->removeElement($outputWorkflow);
            $outputWorkflow->setFormDefinition(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputWorkflows()
    {
        return $this->outputWorkflows;
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

        if (isset($this->fields[$name])) {
            if (!$this->fields[$name] instanceof FormFieldDynamicDefinitionInterface) {
                throw new \Exception(sprintf('"%s" as field name is already used by form builder fields.', $name));
            }
        }

        $dynamicField = new FormFieldDynamicDefinition($name, $type, $options, $optional);
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

        if (isset($this->fields[$name]) && $this->fields[$name] instanceof FormFieldDynamicDefinitionInterface) {
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
    public function getField(string $name, bool $deep = false)
    {
        return $this->findField($this->getFields(), $name, $deep);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType(string $name, bool $deep = false)
    {
        $field = $this->findField($this->getFields(), $name, $deep);

        if ($field === null) {
            return null;
        }

        return $field->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldContainer(string $name)
    {
        $fieldContainerDefinition = $this->getField($name);
        if ($fieldContainerDefinition !== null && !$fieldContainerDefinition instanceof FormFieldContainerDefinitionInterface) {
            throw new \Exception(sprintf('Requested field "%s" container is not an instance of FormFieldContainerDefinitionInterface', $name));
        }

        return $fieldContainerDefinition;
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
     * @param FormFieldDefinitionInterface[]       $fields
     * @param                                      $value
     * @param bool                                 $deep
     *
     * @return FormFieldDefinitionInterface|null
     */
    protected function findField(array $fields, $value, bool $deep = false)
    {
        foreach ($fields as $field) {

            if ($field->getName() === $value) {
                return $field;
            }

            if ($deep === true && $field instanceof FormFieldContainerDefinitionInterface) {
                $subField = $this->findField($field->getFields(), $value, $deep);
                if ($subField !== null) {
                    return $subField;
                }
            }
        }

        return null;
    }
}
