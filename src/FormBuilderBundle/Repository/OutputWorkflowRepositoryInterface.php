<?php

namespace FormBuilderBundle\Repository;

use FormBuilderBundle\Model\OutputWorkflowInterface;

interface OutputWorkflowRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|OutputWorkflowInterface
     */
    public function findById($id);

    /**
     * @param string $name
     * @param int    $formId
     *
     * @return null|OutputWorkflowInterface
     */
    public function findByNameAndFormId(string $name, int $formId);

    /**
     * @param int $id
     *
     * @return null|OutputWorkflowInterface
     */
    public function findNameById($id);

    /**
     * @param string $name
     *
     * @return null|OutputWorkflowInterface
     */
    public function findIdByName(string $name);

    /**
     * @return OutputWorkflowInterface[]
     */
    public function findAll();
}
