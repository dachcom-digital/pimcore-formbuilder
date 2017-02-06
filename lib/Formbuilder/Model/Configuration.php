<?php

namespace Formbuilder\Model;

use Pimcore\Tool;
use Pimcore\Model;

class Configuration extends Model\AbstractModel
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $data;

    /**
     * @var integer
     */
    public $creationDate;

    /**
     * @var integer
     */
    public $modificationDate;

    /**
     * this is a small per request cache to know which configuration is which is, this info is used in self::getByKey()
     * @var array
     */
    protected static $nameIdMappingCache = [];

    /**
     * @param integer $id
     *
     * @return Configuration
     */
    public static function getById($id)
    {
        $cacheKey = 'formbuilder_configuration_' . $id;

        try {
            $configurationEntry = \Zend_Registry::get($cacheKey);
            if (!$configurationEntry) {
                throw new \Exception('Configuration in registry is null');
            }
        } catch (\Exception $e) {
            try {
                $configurationEntry = new self();
                \Zend_Registry::set($cacheKey, $configurationEntry);
                $configurationEntry->setId(intval($id));
                $configurationEntry->getDao()->getById();
            } catch (\Exception $e) {
                \Pimcore\Logger::error($e);

                return NULL;
            }
        }

        return $configurationEntry;
    }

    /**
     * @param string  $key
     * @param boolean $returnObject
     *
     * @return mixed|null
     */
    public static function get($key, $returnObject = FALSE)
    {
        $cacheKey = $key . '~~~';

        if (array_key_exists($cacheKey, self::$nameIdMappingCache)) {
            $entry = self::getById(self::$nameIdMappingCache[$cacheKey]);

            if ($returnObject) {
                return $entry;
            }

            return $entry instanceof Configuration ? $entry->getData() : NULL;
        }

        $configurationEntry = new self();

        try {
            $configurationEntry->getDao()->getByKey($key);
        } catch (\Exception $e) {
            return NULL;
        }

        if ($configurationEntry->getId() > 0) {
            self::$nameIdMappingCache[$cacheKey] = $configurationEntry->getId();
            $entry = self::getById($configurationEntry->getId());

            if ($returnObject) {
                return $entry;
            }

            return $entry instanceof Configuration ? $entry->getData() : NULL;
        }
    }

    /**
     * set data for key
     *
     * @param $key
     * @param $data
     */
    public static function set($key, $data)
    {
        $configEntry = self::get($key, TRUE);

        if (!$configEntry) {
            $configEntry = new self();
            $configEntry->setKey($key);
        }

        $configEntry->setData($data);
        $configEntry->save();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return int
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }
}
