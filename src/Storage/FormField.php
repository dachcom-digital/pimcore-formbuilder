<?php

namespace FormBuilderBundle\Storage;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;

class FormField
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
    private $width;

    /**
     * @var int
     */
    private $order;

    /**
     * @var array
     */
    private $options;

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return FormField
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return FormField
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return FormField
     */
    public function setDisplayName($name)
    {
        $this->display_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @param string $type
     *
     * @return FormField
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $width
     *
     * @return FormField
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param array $options
     */
    public function setOptions($options = [])
    {
        $this->options = $options;
    }

    /**
     * @return FormTypeOptionsMapper
     */
    public function getOptions()
    {
        $options = new FormTypeOptionsMapper($this->options);
        return $options;
    }

    public function toArray()
    {
        $vars = get_object_vars($this);
        $array = [];
        foreach ($vars as $key => $value) {
            $array[ltrim($key, '_')] = $value;
        }

        return $array;
    }
}
