<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface RuntimeDataProviderInterface
{
    public function getRuntimeDataId(): string;

    public function hasRuntimeData(FormDefinitionInterface $formDefinition): bool;

    public function getRuntimeData(FormDefinitionInterface $formDefinition): mixed;
}
