<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Model\FormDefinitionInterface;

/**
 * @deprecated since version 3.3, to be removed in 4.0; use FormBuilderBundle\Model\FieldDefinitionInterface instead.
 */
class FormManager extends FormDefinitionManager
{
    /**
     * @param int $id
     *
     * @return FormDefinitionInterface|null
     *
     * @deprecated
     */
    public function getById(int $id)
    {
        return parent::getById($id);
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @deprecated
     */
    public function configurationFileExists(int $id)
    {
        return parent::configurationFileExists($id);
    }

    /**
     * @param int $id
     *
     * @return string
     *
     * @deprecated
     */
    public function getConfigurationPath(int $id)
    {
        return parent::getConfigurationPath($id);
    }

    /**
     * @return FormDefinitionInterface[]
     *
     * @deprecated
     */
    public function getAll()
    {
        return parent::getAll();
    }

    /**
     * @param string $name
     *
     * @return FormDefinitionInterface|null
     *
     * @deprecated
     */
    public function getIdByName(string $name)
    {
        return parent::getIdByName($name);
    }

    /**
     * @param array $data
     * @param null  $id
     *
     * @return FormDefinitionInterface|null
     * @throws \Exception
     *
     * @deprecated
     */
    public function save(array $data, $id = null)
    {
        return parent::save($data, $id);
    }

    /**
     * @param int $id
     *
     * @return void|null
     *
     * @deprecated
     */
    public function delete($id)
    {
        parent::delete($id);

        return null;
    }

    /**
     * @param int    $id
     * @param string $newName
     *
     * @return FormDefinitionInterface|null
     * @throws \Exception
     *
     * @deprecated
     */
    public function rename(int $id, string $newName)
    {
        return parent::rename($id, $newName);
    }
}
