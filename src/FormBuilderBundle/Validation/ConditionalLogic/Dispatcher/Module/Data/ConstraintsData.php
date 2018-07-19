<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

class ConstraintsData implements DataInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @inheritdoc
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function hasData()
    {
        return !empty($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }
}