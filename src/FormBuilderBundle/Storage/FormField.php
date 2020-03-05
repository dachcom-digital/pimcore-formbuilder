<?php

namespace FormBuilderBundle\Storage;

class FormField implements FormFieldInterface
{
    /**
     * @var bool
     */
    protected $update = false;

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
     * @var int
     */
    private $order;

    /**
     * @var array
     */
    private $constraints = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $optional = [];

    /**
     * @param bool $update
     */
    public function __construct($update = false)
    {
        $this->update = $update;
    }

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
    public function isUpdated()
    {
        return $this->update;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = [])
    {
        $this->options = array_filter($options, function ($option) {
            return $option !== '';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptional(array $options = [])
    {
        $this->optional = array_filter($options, function ($option) {
            return $option !== '';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * {@inheritdoc}
     */
    public function setConstraints(array $constraints = [])
    {
        $this->constraints = $constraints;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        return $this->constraints;
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

        $removeKeys = ['update'];

        return array_diff_key($array, array_flip($removeKeys));
    }
}
