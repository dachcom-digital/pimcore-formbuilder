<?php

namespace Formbuilder\Model\Configuration\Listing;

use Pimcore;
use Formbuilder\Model;

class Dao extends Pimcore\Model\Dao\PhpArrayTable
{
    public function configure()
    {
        parent::configure();
        $this->setFile('formbuilder_configurations');
    }

    /**
     * Loads a list of Configurations for the specicifies parameters, returns an array of Configuration elements
     * @return array
     */
    public function load()
    {
        $routesData = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());

        $routes = [];
        foreach ($routesData as $routeData) {
            $routes[] = Model\Configuration::getById($routeData['id']);
        }

        $this->model->setConfigurations($routes);

        return $routes;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        $data = $this->db->fetchAll($this->model->getFilter(), $this->model->getOrder());
        $amount = count($data);

        return $amount;
    }
}
