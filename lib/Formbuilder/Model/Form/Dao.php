<?php

namespace Formbuilder\Model\Form;

use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\AbstractModel;

class Dao extends AbstractDao
{
    protected $tableName = 'formbuilder_forms';

    /**
     * @param null $name
     * @throws \Exception
     */
    public function getByName($name = NULL)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName .' WHERE name = ?', $name);

        if (!$data['id'])
        {
            throw new \Exception('Object with the name ' . $name . ' doesn\'t exists');
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @param null $id
     * @throws \Exception
     */
    public function getById($id = NULL)
    {
        if ($id != NULL)
        {
            $this->model->setId($id);
        }

        $data = $this->db->fetchRow('SELECT * FROM '. $this->tableName .' WHERE id = ?', $this->model->getId());

        if (isset($data['id']))
        {
            $this->assignVariablesToModel($data);
        }
        else
        {
            throw new \Exception('Form with id: ' . $this->model->getId() . ' doesn\'t exist');
        }
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    public function save()
    {
        $vars = get_object_vars($this->model);

        $buffer = [];

        $validColumns = $this->getValidTableColumns($this->tableName);

        if ( count($vars) )
        {
            foreach ( $vars as $k => $v )
            {
                if( !in_array($k, $validColumns) )
                {
                    continue;
                }

                $getter = 'get' . ucfirst($k);

                if( !is_callable( [$this->model, $getter] ) )
                {
                    continue;
                }

                $value = $this->model->$getter();

                if( is_bool($value) )
                {
                    $value = (int)$value;
                }

                if( is_array($value) )
                {
                    $value = serialize($value);
                }

                if($value instanceof AbstractObject || $value instanceof AbstractModel)
                {
                    $value = $value->getId();
                }

                if( is_object($value) )
                {
                    $value = serialize($value);
                }

                $buffer[$k] = $value;
            }
        }

        if ($this->model->getId() !== NULL)
        {
            $this->db->update($this->tableName, $buffer, $this->db->quoteInto('id = ?', (int) $this->model->getId()));
            return;
        }

        $this->db->insert($this->tableName, $buffer);
        $this->model->setId($this->db->lastInsertId());

    }

    public function delete()
    {
        try
        {
            $this->db->delete($this->tableName, $this->db->quoteInto('id = ?', (int) $this->model->getId()));
        }
        catch (\Exception $e)
        {
            throw $e;
        }

        return TRUE;

    }
}