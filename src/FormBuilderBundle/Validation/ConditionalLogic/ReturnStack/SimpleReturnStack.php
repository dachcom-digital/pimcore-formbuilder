<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class SimpleReturnStack implements ReturnStackInterface
{
    /**
     * @var NULL|string
     */
    public $actionType;

    /**
     * @var array
     */
    public $data = [];

    /**
     * EmptyReturnStack constructor.
     *
     * @param NULL|string $actionType
     * @param array       $data
     */
    public function __construct($actionType = null, $data = [])
    {
        $this->actionType = $actionType;
        $this->data = $data;
    }

    /**
     * @return NULL|string
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
