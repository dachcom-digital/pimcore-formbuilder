<?php

namespace FormBuilderBundle\Storage\Form;

use Pimcore\Model;
use FormBuilderBundle\Storage\Form\Listing\Dao as ListingDao;

class Listing extends Model\Listing\AbstractListing
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Test if the passed key is valid.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @deprecated
     *
     * @param array $data
     *
     * @return $this|Listing
     */
    public function setFormBuilderData($data)
    {
        if (method_exists($this, 'setData')) {
            return $this->setData($data);
        }

        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->data === null) {
            $entities = [];
            foreach ($this->getDao()->load() as $o) {
                $entities[] = $o;
            }

            $this->data = $entities;
        }

        return $this->data;
    }

    /**
     * @return ListingDao
     */
    public function getDao()
    {
        return parent::getDao();
    }
}
