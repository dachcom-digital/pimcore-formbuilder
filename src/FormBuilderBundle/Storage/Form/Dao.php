<?php

namespace FormBuilderBundle\Storage\Form;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Storage\FormFieldInterface;
use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\DataObject\AbstractObject;
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
    public function getByName($name = null)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE name = ?', $name);

        if (!$data['id']) {
            throw new \Exception(sprintf('Form with name "%s" does not exist.', $name));
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @param null $id
     *
     * @throws \Exception
     */
    public function getById($id = null)
    {
        if ($id != null) {
            $this->model->setId($id);
        }

        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE id = ?', $this->model->getId());

        if (isset($data['id'])) {
            $this->assignVariablesToModel($data);
        } else {
            throw new \Exception(sprintf('Form with id "%d" does not exist.', $this->model->getId()));
        }
    }

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

        if ($this->model->getId() !== null) {
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

        return true;
    }

    protected function storeFormData()
    {
        $data = [
            'config'            => $this->model->getConfig(),
            'conditional_logic' => $this->model->getConditionalLogic(),
            'fields'            => $this->getFormFieldData()
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