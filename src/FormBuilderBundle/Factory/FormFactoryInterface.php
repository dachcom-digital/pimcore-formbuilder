<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;

interface FormFactoryInterface
{
    /**
     * @return FormInterface
     */
    public function createForm();

    /**
     * @param int  $id
     * @param bool $ignoreMissingConfigurationFile
     *
     * @return null|FormInterface
     */
    public function getFormById($id, bool $ignoreMissingConfigurationFile = true);

    /**
     * @param string $name
     * @param bool $ignoreMissingConfigurationFile
     *
     * @return null|FormInterface
     */
    public function getFormIdByName(string $name, bool $ignoreMissingConfigurationFile = true);

    /**
     * @return FormInterface[]
     */
    public function getAllForms();

    /**
     * @return FormFieldInterface
     */
    public function createFormField();

    /**
     * @param FormInterface $formEntity
     *
     * @throws \Exception
     */
    public function assignRelationDataToFormObject(FormInterface $formEntity);

    /**
     * @param int $formId
     *
     * @return bool
     */
    public function formHasAvailableConfigurationFile(int $formId);

    /**
     * @param int $formId
     *
     * @return string
     */
    public function getConfigurationPathOfForm(int $formId);
}