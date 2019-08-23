<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\ReturnStack;

class FieldReturnStack implements ReturnStackInterface
{
    /**
     * @var string
     */
    public $actionType;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param string $actionType
     * @param array  $data
     *
     * @throws \Exception
     */
    public function __construct($actionType = null, $data = [])
    {
        if ($this->isAssoc($this->data)) {
            throw new \Exception('FieldReturnStack: Wrong data structure: data keys must contain form field names!');
        }

        $this->actionType = $actionType;
        $this->data = $data;
    }

    /**
     * @return string
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

    /**
     * @param array $data
     */
    public function updateData($data)
    {
        $this->data = $data;
    }

    /**
     * @param array $arr
     *
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
