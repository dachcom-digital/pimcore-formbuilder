<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\FormInterface;

interface FormRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|FormInterface
     */
    public function findById($id);

    /**
     * @param string $name
     *
     * @return null|FormInterface
     */
    public function findByName(string $name);

    /**
     * @param int $id
     *
     * @return null|FormInterface
     */
    public function findNameById($id);

    /**
     * @param string $name
     *
     * @return null|FormInterface
     */
    public function findIdByName(string $name);

    /**
     * @return FormInterface[]
     */
    public function findAll();
}
