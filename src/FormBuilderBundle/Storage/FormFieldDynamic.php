<?php

namespace FormBuilderBundle\Storage;

class FormFieldDynamic implements FormFieldDynamicInterface
{
    /**
     * @var bool
     */
    protected $name = FALSE;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $update = FALSE;

    /**
     * @var string
     */
    protected $options;

    /**
     * @var string
     */
    protected $optional;

    /**
     * FormFieldDynamic constructor.
     *
     * @param $name
     * @param $type
     * @param $options
     * @param $optional
     * @param $update
     */
    public function __construct($name, $type, $options, $optional = [], $update = FALSE)
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->optional = $optional;
        $this->update = $update;
    }

    /**
     * @return bool
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isUpdated()
    {
        return $this->update;
    }

    /**
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array|string
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * @return int|mixed
     */
    public function getOrder()
    {
        $optional = $this->getOptional();

        if (isset($optional['order'])) {
            return $optional['order'];
        }

        return 0;
    }
}
