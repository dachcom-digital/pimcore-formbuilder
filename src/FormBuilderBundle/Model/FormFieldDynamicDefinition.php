<?php

namespace FormBuilderBundle\Model;

class FormFieldDynamicDefinition implements FormFieldDynamicDefinitionInterface
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
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param array  $optional
     */
    public function __construct(string $name, string $type, array $options, array $optional = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->optional = $optional;
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
