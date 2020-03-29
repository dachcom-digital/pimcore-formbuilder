<?php

namespace FormBuilderBundle\Form\RuntimeData;

class RuntimeDataCollector
{
    /**
     * @var array
     */
    protected $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @param string $id
     * @param mixed  $data
     *
     * @throws \Exception
     */
    public function add($id, $data)
    {
        if (array_key_exists($id, $this->data)) {
            throw new \Exception(sprintf('Runtime Data Block with "%s" already added.', $id));
        }

        $this->data[$id] = $data;
    }

    /**
     * @param string $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function find($id)
    {
        if (!array_key_exists($id, $this->data)) {
            throw new \Exception(sprintf('Runtime Data Block with "%s" not found.', $id));
        }

        return $this->data[$id];
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (count($this->data) === 0) {
            return '';
        }

        return json_encode($this->data);
    }
}
