<?php

namespace Formbuilder\Model\Configuration;

use Formbuilder\Model\Configuration;
use Pimcore\Model;

class Listing extends Model\Listing\JsonListing
{

    /**
     * Contains the results of the list. They are all an instance of Configuration
     *
     * @var array
     */
    public $configurations = NULL;

    /**
     * @return Configuration[]
     */
    public function getConfigurations()
    {
        if (is_null($this->configurations))
        {
            $this->load();
        }

        return $this->configurations;
    }

    /**
     * @param array $configurations
     * @return void
     */
    public function setConfigurations($configurations)
    {
        $this->configurations = $configurations;
    }
}
