<?php

namespace Formbuilder\Model\Form\Listing;

use Pimcore\Model\Listing;

class Dao extends Listing\Dao\AbstractDao
{
    protected $tableName = 'formbuilder_forms';

    protected $modelClass = '\\Formbuilder\\Model\\Form';

    public function load()
    {
        $objects = $this->db->fetchAll('SELECT * FROM ' . $this->tableName);

        $this->model->setData($objects);

        return $objects;
    }
}
