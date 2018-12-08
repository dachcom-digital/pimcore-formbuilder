<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class SimpleReturnStack implements ReturnStackInterface
{
    /**
     * @var null|string
     */
    public $actionType;

    /**
     * @var array
     */
    public $data = [];

    /**
     * EmptyReturnStack constructor.
     *
     * @param null|string $actionType
     * @param array       $data
     */
    public function __construct($actionType = null, $data = [])
    {
        $this->actionType = $actionType;
        $this->data = $data;
    }

    /**
     * @return null|string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
