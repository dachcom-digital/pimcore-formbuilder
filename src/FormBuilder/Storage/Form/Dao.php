<?php

namespace FormBuilderBundle\Storage\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Storage\FormFieldInterface;
use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\AbstractModel;
use Symfony\Component\Yaml\Yaml;

class Dao extends AbstractDao
{
    /**
     * @var string
     */
    protected $tableName = 'formbuilder_forms';

    /**
     * @param null $name
     *
     * @throws \Exception
     */
    public function getByName($name = NULL)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE name = ?', $name);

        if (!$data['id']) {
            throw new \Exception('Form with ID ' . $name . ' doesn\'t exists');
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @param null $id
     *
     * @throws \Exception
     */
    public function getById($id = NULL)
    {
        if ($id != NULL) {
            $this->model->setId($id);
        }

        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE id = ?', $this->model->getId());

        if (isset($data['id'])) {
            $this->assignVariablesToModel($data);
        } else {
            throw new \Exception('Form with id: ' . $this->model->getId() . ' doesn\'t exist');
        }
    }

    /**
     *
     */
    public function save()
    {
        $vars = get_object_vars($this->model);

        $buffer = [];

        $validColumns = $this->getValidTableColumns($this->tableName);

        if (count($vars)) {
            foreach ($vars as $k => $v) {
                if (!in_array($k, $validColumns)) {
                    continue;
                }

                $getter = 'get' . ucfirst($k);

                if (!is_callable([$this->model, $getter])) {
                    continue;
                }

                $value = $this->model->$getter();

                if (is_bool($value)) {
                    $value = (int)$value;
                }

                if (is_array($value)) {
                    $value = serialize($value);
                }

                if ($value instanceof AbstractObject || $value instanceof AbstractModel) {
                    $value = $value->getId();
                }

                if (is_object($value)) {
                    $value = serialize($value);
                }

                $buffer[$k] = $value;
            }
        }

        if ($this->model->getId() !== NULL) {
            $this->db->update($this->tableName, $buffer, ['id' => (int)$this->model->getId()]);
        } else {
            $this->db->insert($this->tableName, $buffer);
            $this->model->setId($this->db->lastInsertId());
        }

        $this->storeFormData();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function delete()
    {
        try {
            $this->deleteFormData();
            $this->db->delete($this->tableName, ['id' => (int)$this->model->getId()]);
        } catch (\Exception $e) {
            throw $e;
        }

        return TRUE;
    }

    /**
     *
     */
    protected function storeFormData()
    {
        $data = [
            'config' => $this->model->getConfig(),
            'fields' => $this->getFormFieldData()
        ];

        return $this->storeYmlData($data);
    }

    /**
     * @return array
     */
    protected function getFormFieldData()
    {
        $formFields = [];

        /** @var FormFieldInterface $field */
        foreach ($this->model->getFields() as $field) {
            $formFields[] = $field->toArray();
        }

        return $formFields;
    }

    /**
     *
     */
    protected function deleteFormData()
    {
        if (file_exists(Configuration::STORE_PATH . '/main_' . $this->model->getId() . '.yml')) {
            unlink(Configuration::STORE_PATH . '/main_' . $this->model->getId() . '.yml');
        }
    }

    /**
     * @param $data
     */
    protected function storeYmlData($data)
    {
        if (file_exists(Configuration::STORE_PATH . '/main_' . $this->model->getId() . '.yml')) {
            unlink(Configuration::STORE_PATH . '/main_' . $this->model->getId() . '.yml');
        }

        $yml = Yaml::dump($data);
        file_put_contents(Configuration::STORE_PATH . '/main_' . $this->model->getId() . '.yml', $yml);
    }
}