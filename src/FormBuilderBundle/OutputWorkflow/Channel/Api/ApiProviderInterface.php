<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface ApiProviderInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     */
    public function getApiConfigurationFields(FormDefinitionInterface $formDefinition);

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     */
    public function getPredefinedApiFields(FormDefinitionInterface $formDefinition);

    /**
     * @param ApiData $apiData
     */
    public function process(ApiData $apiData);
}
