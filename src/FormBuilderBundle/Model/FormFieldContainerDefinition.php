<?php

namespace FormBuilderBundle\Model;

use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;

class FormFieldContainerDefinition implements FormFieldContainerDefinitionInterface, EntityToArrayAwareInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    private $display_name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $sub_type;

    /**
     * @var int
     */
    private $order;

    /**
     * @var array
     */
    private $configuration = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(int $order)
    {
        $this->order = $order;
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
    public function setDisplayName(string $name)
    {
        $this->display_name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubType(string $subType)
    {
        $this->sub_type = $subType;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubType()
    {
        return $this->sub_type;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $configuration = [])
    {
        $this->configuration = array_filter($configuration, function ($configElement) {
            return $configElement !== '';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields = [])
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
    public function toArray()
    {
        $vars = get_object_vars($this);
        $array = [];
        foreach ($vars as $key => $value) {
            $array[ltrim($key, '_')] = $value;
        }

        $removeKeys = ['fields'];
        $data = array_diff_key($array, array_flip($removeKeys));

        // parse fields
        $fieldData = [];
        foreach ($this->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $fieldData[] = $field->toArray();
            }
        }

        $data['fields'] = $fieldData;

        return $data;
    }
}
