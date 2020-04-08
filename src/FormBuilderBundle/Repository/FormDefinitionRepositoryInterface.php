<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDefinitionRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|FormDefinitionInterface
     */
    public function findById($id);

    /**
     * @param string $name
     *
     * @return null|FormDefinitionInterface
     */
    public function findByName(string $name);

    /**
     * @param int $id
     *
     * @return null|FormDefinitionInterface
     */
    public function findNameById($id);

    /**
     * @param string $name
     *
     * @return null|FormDefinitionInterface
     */
    public function findIdByName(string $name);

    /**
     * @return FormDefinitionInterface[]
     */
    public function findAll();
}
