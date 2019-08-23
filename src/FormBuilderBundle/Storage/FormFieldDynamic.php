<?php

namespace FormBuilderBundle\Storage;

class FormFieldDynamic implements FormFieldDynamicInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $optional;

    /**
     * @var bool
     */
    protected $update = false;

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $optional
     * @param bool   $update
     */
    public function __construct(string $name, string $type, array $options, array $optional = [], bool $update = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->optional = $optional;
        $this->update = $update;
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
    public function getOptions()
    {
        return $this->options;
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
    public function getOrder()
    {
        $optional = $this->getOptional();
        if (isset($optional['order']) && is_numeric($optional['order'])) {
            return (int) $optional['order'];
        }

        return 0;
    }
}
