<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\FormInterface;

interface FormFactoryInterface
{
    /**
     * @return FormInterface
     */
    public function createForm();

    /**
     * @param int $id
     *
     * @return null|FormInterface
     */
    public function getFormById($id);

    /**
     * @param string $name
     *
     * @return null|FormInterface
     */
    public function getFormIdByName(string $name);

    /**
     * @return FormInterface[]
     */
    public function getAllForms();

    /**
     * @return FormFieldInterface
     */
    public function createFormField();

    /**
     * @return FormFieldContainerInterface
     */
    public function createFormFieldContainer();

    /**
     * @param FormInterface $formEntity
     *
     * @throws \Exception
     */
    public function assignRelationDataToFormObject(FormInterface $formEntity);

    /**
     * @param FormFieldInterface $formField
     * @param array              $field
     *
     * @return FormFieldInterface|FormFieldContainerInterface
     */
    public function populateFormField($formField, array $field);

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