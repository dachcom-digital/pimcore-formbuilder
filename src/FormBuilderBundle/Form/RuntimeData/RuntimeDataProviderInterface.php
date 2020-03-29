<?php

namespace FormBuilderBundle\Form\RuntimeData;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface RuntimeDataProviderInterface
{

    /**
     * @return string
     */
    public function getRuntimeDataId();

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return bool
     */
    public function hasRuntimeData(FormDefinitionInterface $formDefinition);

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return mixed
     */
    public function getRuntimeData(FormDefinitionInterface $formDefinition);
}
