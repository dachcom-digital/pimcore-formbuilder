<?php

namespace FormBuilderBundle\Storage\DataConnector;

use FormBuilderBundle\Model\FormInterface;
use FormBuilderBundle\Storage\FormFieldContainerInterface;
use FormBuilderBundle\Storage\FormFieldInterface;

interface FormDataConnectorInterface
{
    /**
     * @param FormInterface $formEntity
     *
     * @throws \Exception
     */
    public function assignRelationDataToFormObject(FormInterface $formEntity);

    /**
     * @param FormFieldInterface|FormFieldContainerInterface $formField
     * @param array                                          $field
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

    /**
     * @param FormInterface $form
     */
    public function storeFormData(FormInterface $form);

    /**
     * @param FormInterface $form
     */
    public function deleteFormData(FormInterface $form);
}
