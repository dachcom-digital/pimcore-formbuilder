<?php

namespace FormBuilderBundle\Storage\Form\Listing;

use Pimcore\Model\Listing;
use FormBuilderBundle\Storage\Form\Listing as ListingModel;

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
     * @var ListingModel
     */
    protected $model;

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
