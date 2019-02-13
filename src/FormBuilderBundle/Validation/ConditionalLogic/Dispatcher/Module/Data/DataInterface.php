<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data;

interface DataInterface
{
    /**
     * @param array $data
     */
    public function setData(array $data);

    /**
     * @return bool
     */
    public function hasData();

    /**
     * @return mixed
     */
    public function getData();
}
