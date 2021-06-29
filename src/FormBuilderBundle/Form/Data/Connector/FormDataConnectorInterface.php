<?php

namespace FormBuilderBundle\Form\Data\Connector;

use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;

interface FormDataConnectorInterface
{
    public function assignRelationDataToFormObject(FormDefinitionInterface $formDefinition): void;

    public function populateFormField(FieldDefinitionInterface $formField, array $field): FieldDefinitionInterface;

    public function formHasAvailableConfigurationFile(int $formDefinitionId): bool;

    public function getConfigurationPathOfForm(int $formDefinitionId): string;

    public function storeFormData(FormDefinitionInterface $formDefinition): void;

    public function deleteFormData(FormDefinitionInterface $formDefinition): void;
}
