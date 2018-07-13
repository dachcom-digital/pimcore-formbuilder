<?php

namespace FormBuilderBundle\Storage\Form\Listing;

use Pimcore\Model\Listing;

class Dao extends Listing\Dao\AbstractDao
{
    /**
     * @var string
     */
    protected $tableName = 'formbuilder_forms';

    /**
     * @var string
     */
    protected $modelClass = '\\Formbuilder\\Model\\Form';

    /**
     * @return mixed
     */
    public function load()
    {
        $objects = $this->db->fetchAll('SELECT * FROM ' . $this->tableName);
        $this->model->setData($objects);
        return $objects;
    }
}
