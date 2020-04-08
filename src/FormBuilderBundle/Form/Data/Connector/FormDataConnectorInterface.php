<?php

namespace FormBuilderBundle\Form\Data\Connector;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDataConnectorInterface
{
    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @throws \Exception
     */
    public function assignRelationDataToFormObject(FormDefinitionInterface $formDefinition);

    /**
     * @param FieldDefinitionInterface $formField
     * @param array                    $field
     *
     * @return FieldDefinitionInterface
     */
    public function populateFormField($formField, array $field);

    /**
     * @param int $formDefinitionId
     *
     * @return bool
     */
    public function formHasAvailableConfigurationFile(int $formDefinitionId);

    /**
     * @param int $formDefinitionId
     *
     * @return string
     */
    public function getConfigurationPathOfForm(int $formDefinitionId);

    /**
     * @param FormDefinitionInterface $formDefinition
     */
    public function storeFormData(FormDefinitionInterface $formDefinition);

    /**
     * @param FormDefinitionInterface $formDefinition
     */
    public function deleteFormData(FormDefinitionInterface $formDefinition);
}
